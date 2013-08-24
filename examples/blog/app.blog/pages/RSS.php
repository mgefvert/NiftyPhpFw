<?php

/**
 * This class creates an RSS feed for the articles. (No comments included.)
 */
class RSS extends NF_Page
{
    public function executeView()
    {
        global $Application, $Response, $Persistence;

        // Create a new NF_RSS object with the minimally required info.
        $rss = new NF_RSS($Application->appTitle, $Application->appURL, $Application->appTitle);

        // Set the last build date (which we get from the articles published date.)
        $rss->setLastBuildDate(Data_BlogEntry::getMaxPubDate());

        // Load the latest 10 entries.
        $entries = Data_BlogEntry::loadEntries(0, 10, true);

        if ($entries)
            foreach($entries as $entry)
            {
                // Create new NF_RSS_Item objects and add them to the feed.
                $item = new NF_RSS_Item();
                $item->setTitle(NF_Text::toUnicode($entry->title)); // Everything has to be Unicode.
                $item->setPubDate($entry->created);
                $item->setGuid($Application->appURL . 'index/view/' . $entry->id, true); // Should link to the "view item" page.
                $item->setContent(NF_Text::toUnicode(strip_tags($entry->text)));

                $rss->addItem($item);
            }

        // $Response->reset() clears the master template info, so we don't get any
        // further handling. It means that whatever we put into $Response->content
        // is what is sent to the user - byte for byte, no extra handling.
        $Response->reset();
        $Response->contentType = 'text/xml';  // Set the content type. Could be "application/rss+xml" but seems to work so-so.
        $Response->content = $rss->getXml();  // Render the feed to XML.
    }
}
