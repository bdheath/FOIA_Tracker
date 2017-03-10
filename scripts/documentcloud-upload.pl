#!/perl/bin

use LWP::UserAgent;
use URI::Escape;
use DBI;
use WWW::Mechanize;


my $user = ""; # YOUR DC USERNAME (USE %40 for @ - i.e., bheath%40usatoday.com
my $pass = "" # YOUR DC PASSWORD
my $connstring = ''; # YOUR MYSQL CONNECTION STRING (DBI)

my $contents;

my $db = DBI->connect($connstring,'DBUSERNAME','DBPASSWORD');
my $sql = "SELECT di.title as doctitle, di.foia_id as foia_id, r.title as requesttitle, di.filename_mod, di.document_id FROM foia.documents_index AS di INNER JOIN foia.requests AS r USING (foia_id) WHERE dc_id IS NULL AND (dc_tries IS NULL OR dc_tries <= 10) ";
my $result = $db->prepare($sql);
$result->execute;

my $browser = LWP::UserAgent->new;

while($h = $result->fetchrow_hashref) {

	my $s = "SELECT UNCOMPRESS(document) AS doc FROM foia.documents WHERE document_id = " . $$h{'document_id'};
	my $r2 = $db->prepare($s);
	$r2->execute;
	my $doc = $r2->fetchrow_hashref;
	
	my $d = $$doc{'doc'};
		
	my $title = $$h{'requesttitle'} . " - " . $$h{'doctitle'};
	$title =~ s/\\([^\\])/$1/g;
	
	print " -> " . $title . "\n";
	my $r = $browser->post( 'https://$user:$pass\@www.documentcloud.org/api/upload/',
		[ 
			file => [ undef, $$h{'filename_mod'}, Content => $d ],
			title => $title,
			access => 'organization',
			description => $$h{'citation'},
		],
		Content_Type => 'multipart/form-data'
	);
	my $resp = $r->content;
	
	if($resp =~ m/id\":\"(.*?)\"/ig) {
		# Good request - add to db
		my $id = $1;
		my $url = "http://www.documentcloud.org/documents/" . $id;
		$resp =~ m/thumbnail\":\"(.*?)\"/ig;
		my $thumb = $1;
		my $s = "UPDATE foia.documents_index SET "
			. " dc_id = " . $db->quote($id) . ","
			. " dc_url = " . $db->quote($url) . ","
			. " dc_tries = dc_tries + 1, "
			. " dc_last_try = NOW() "
			. " WHERE document_id = " . $$h{'document_id'}
			. "";
		my $z = $db->do($s);
		
		my $s = "INSERT DELAYED INTO foia.log(event,username,event_time,document_id,foia_id) "
			. " VALUES('Automatic Upload to DocumentCloud','sys',NOW(), "
			. $$h{'document_id'} . ", " . $$h{'foia_id'}
			. ")";
		my $z = $db->do($s);
	}

}


# PARSE RESULTS

my $browser = WWW::Mechanize->new();
$browser->agent_alias("Windows Mozilla");

my $ua = LWP::UserAgent->new;
my $sql = "SELECT title, foia_id, document_id, dc_id, IF(now() >= ADDDATE(dc_last_try, INTERVAL 12 HOUR),1,0) AS e FROM foia.documents_index WHERE dc_id IS NOT NULL AND (dc_exists IS NULL OR dc_exists = 0)  ";
my $r = $db->prepare($sql);
$r->execute;

my $urlBase = "https://$user:$pass\@www.documentcloud.org/api/documents/";



while($h = $r->fetchrow_hashref) {
	print " <- " . $$h{'title'} . "\n";
	my $url = $urlBase . $$h{'dc_id'} . ".json";
	if($$h{'e'} == 1) {
		# give up after 12 hours - call it a failure
		my $s = "UPDATE foia.documents_index SET dc_id = NULL, dc_url = NULL WHERE document_id = " . $$h{document_id};
		my $z = $db->do($s) or die($s);
	} else { 

		$browser->get($url);
		my $json = $browser->content;
		
		$json =~ m/pages\":(.*?),/ig;
		my $pages = $1;
		if($pages == 0) {
		} else {
			my $dcid = $$h{'dc_id'};
			$dcid =~ m/^([0-9]{4,})\-(.*?)$/ig;
			$dcid = $1;
			$dcfn = $2;
			$url = "https://$user:$pass\@www.documentcloud.org/documents/" . $dcid . "/" . $dcfn . ".txt";
			$browser->get($url);
			my $content = $browser->content;
			
			print "    - Updating document index\n";
			my $s = "UPDATE foia.documents_index SET dc_exists = 1, dc_body=" . $db->quote($content) . " WHERE document_id = " .  $$h{'document_id'} ." ";
			my $z = $db->do($s);

			print "    - Updating log\n";			
			my $s = "INSERT DELAYED INTO foia.log(event,username,event_time,document_id,foia_id) "
				. " VALUES('Retrieve OCR Text From DocumentCloud','sys',NOW(), "
				. $$h{'document_id'} . ", " . $$h{'foia_id'}
				. ")";
			my $z = $db->do($s);
			
			print "    - Cleaning text search\n";
			my $s = "DELETE FROM foia.textsearch WHERE foia_id = " . $$h{'foia_id'} . " and item_type = 2 AND document_id = " . $$h{'document_id'} . " ";
			my $z = $db->do($s);
			
			print "    - Updating text search index\n";
			my $s = "INSERT INTO foia.textsearch(foia_id,item_type,document_id,item) VALUES(" . $$h{'foia_id'} . ",2," . $$h{document_id} . "," . $db->quote($content) . ")";
			my $z = $db->do($s) or die("SOMETHING WENT WRONG WITH THE TEXT SERACH SAVE\n\n$s\n\n");
			
		}
	}
}


