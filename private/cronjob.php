<?php
require_once "site.php";
$coffee_conn->setDebug(false);

$sub_arxivs = get_variable("arxivs");


print "<p>Running daily paper import/expire tasks.</p>\n\n";

/* ***************** *
 * Import new papers *
 * ***************** */

function xml2assoc(&$xml)
{
    $allowed = array(
        "item" => "item",
        "title" => "title",
        "description" => "description",
        "dc:creator" => "dc:creator",
        "rdf:RDF" => "rdf:RDF",
        "dc:date" => "dc:date",
        "channel" => "channel",
    );

    // convert XML to associative array
    $assoc = null;
    while ($xml->read()) {
        if ($xml->nodeType == XMLReader::END_ELEMENT) {
            break;
        }

        if ($xml->nodeType == XMLReader::ELEMENT and !$xml->isEmptyElement) {
            $name = $xml->name;
            if (isset($allowed[$name])) {
                if ($name == "item") {
                    $assoc["items"][] = xml2assoc($xml);
                } else {
                    $assoc[$name] = xml2assoc($xml);
                }
            } else {
                // keep processing but don't actually store
                xml2assoc($xml);
            }
        } else if ($xml->nodeType == XMLReader::TEXT) {
            $assoc = $xml->value;
        }
    }

    return $assoc;
}

$success = array();
$missing = array();
$duplicates = array();
$messages = array();
$donate_str = array('<p><a href="http://arxiv.org">Donate to arXiv</a></p>',"<p>DONATE to arXiv: One hundred percent of your contribution will fund improvements and new initiatives to benefit arXiv's global scientific community. Please join the Simons Foundation and our generous member organizations and research labs in supporting arXiv. https://goo.gl/QIgRpr</p>");
$donate_replace = array_fill(0, count($donate_str), '');


foreach ($sub_arxivs as $arxiv) {
    $full_url = ARXIV_RSS_BASE_URL . $arxiv;
    $messages[] = "Importing " . $full_url . " ...";
    echo "<!-- <pre>";
    readfile($full_url) ;
    echo "</pre> -->";
    $xml = new XMLReader();
    if ($xml->open($full_url)) {
        $assoc = xml2assoc($xml);
        $date = strtotime($assoc["rdf:RDF"]["channel"]["dc:date"]);
        $system_date = strtotime("Tomorrow", $date);
        $mysql_date = date("Y-m-d H:i:s", $system_date);
        $messages[] = "Date of RSS feed: " . date("Y-m-d H:i", $date) . "; importing to " . date("Y-m-d H:i", $system_date);
        $items = $assoc["rdf:RDF"]["items"];
        foreach ($items as $article) {
            if (isset($article["title"]) && isset($article["description"]) && isset($article["dc:creator"])) {
                $title = strip_tags(trim($article["title"]));
                $abstract = strip_tags(trim(str_replace($donate_str, $donate_replace, $article["description"])));
                $authors = trim($article["dc:creator"]);
                
                $article_data = array();
                preg_match('/(.*)\s\(arxiv\:(.*)\s\[(.*)\](.*)\)(.*)/i', $title, $article_data);
                if (isset($article_data[2])) {
                    $arxiv_id = $article_data[2];
                } else {
                    $arxiv_id = substr($title, 0, 11); // something unique-ish
                }

                $duplicate_papers = $coffee_conn->boundQuery(
                    "SELECT * FROM papers WHERE arxivId = ? LIMIT 1",
                    array('s', &$arxiv_id)
                );
                if (count($duplicate_papers) > 0) {
                    $duplicates[] = "Paper already exists: `" . $title . "`";
                } else {
                    $coffee_conn->boundCommand(
                        "INSERT INTO papers (title, authors, abstract, subject, arxivId, date) VALUES (?, ?, ?, ?, ?, ?)",
                        array('ssssss', &$title, &$authors, &$abstract, &$arxiv, &$arxiv_id, &$mysql_date)
                    );
                    $success[] = "Imported paper: " . $title;
                }
            } else {
                $missing[] = "Item missing data in " . $arxiv . ": " . serialize($article);
            }
        }
    } else {
        $messages[] = "Failed to read RSS feed for " . $arxiv . ".";
    }
    $xml->close();
}

print "<pre>\n";
foreach ($messages as $message) {
    print $message . "\n";
}
print "\n";
print "Imported " . count($success) . " artricles.\n";
print "Did not import " . count($duplicates) . " duplicate articles.\n";
print "Failed to import " . count($missing) . " artricles missing data.\n";

print "</pre>\n";


/* ***************** *
 * Remove old papers *
 * ***************** */

$expire_date = date(
    "Y-m-d",
    strtotime(
        get_variable("expire_date")
    )
);

$select_statement = "SELECT * FROM papers WHERE papers.date < ? AND papers.id NOT IN (SELECT votes.paperId FROM votes)";
$delete_statement = "DELETE FROM papers WHERE papers.date < ? AND papers.id NOT IN (SELECT votes.paperId FROM votes)";
$papers = $coffee_conn->boundQuery($select_statement, array('s', &$expire_date));
$coffee_conn->boundCommand($delete_statement, array('s', &$expire_date));

print "<pre>\n";
print count($papers) . " papers from before " . $expire_date . " that had NOT been voted on have been removed.\n";
print "</pre>\n";

print "\n<a href='".path()."admin.php'>Visit the admin page</a>.\n\n";
