<?php

class Slug
{
    public function convertAction()
    {
        $request = $this->request->getPost();
        $link = (isset($request['link'])) ? $request['link'] : '';
        $text = self::convert_slug($link);
        $count = Category::count("slug = '{$text}'");
        if ($count > 0) {
            $text .= '-'.($count + 1);
        }
        //		var_dump($count);exit;
        $response = new Response();
        $response->setContent($text);
        $response->send();
    }

    public static function convert_slug($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
