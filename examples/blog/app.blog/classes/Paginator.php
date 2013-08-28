<?php

/**
 * Paginator is just a little utility class for providing HTML output
 * for pagination. Since we have pagination on several pages, we just
 * load this little thing and bam! we have pagination input.
 *
 * Depends on the "pg" parameter on the URL.
 */
class Paginator
{
    public static function render($itemsPerPage, $maxItems)
    {
        $pages = (int)(($maxItems + $itemsPerPage - 1) / $itemsPerPage);

        if ($pages > 1)
            echo NF_Template::run('misc/paginator.phtml', array(
                'page'  => NF::request()->pg,
                'pages' => $pages
            ));
    }
}
