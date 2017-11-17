<?php
/**
 * Content parser
 * @package content
 * @version 0.0.1
 * @upgrade true
 */

namespace Content\Library;

class Parser {

    static function getComponents(String $html){
        $components = [
            'facebook-video'    => 'facebook_js_tag',
            'facebook-post'     => 'facebook_js_tag',
            'instagram-post'    => 'instagram_js_embed',
            'instagram-video'   => 'instagram_js_embed',
            'twitter'           => 'twitter_js_embed',
            'googleplus'        => 'google_js_tag'
        ];
        
        preg_match_all('!embed embed-([a-z-]+)!', $html, $out);
        
        if(!$out)
            return [];
        
        if(!isset($out[1]) || !$out[1])
            return [];
        
        $result = [];
        foreach($out[1] as $cmp){
            if(isset($components[$cmp]))
                $result[] = $components[$cmp];
        }
        return array_unique($result);
    }
    
    static function _fixImage($doc){
        $imgs = $doc->getElementsByTagName('img');
        if(!$imgs->length)
            return;
        
        foreach($imgs as $img){
            $src = $img->getAttribute('src');
            $src = preg_replace('!^[\.\/]+!', '/', $src);
            $src = preg_replace('!https?:!', '', $src);
            $img->setAttribute('src', $src);
            
            $cls = $img->getAttribute('class');
            $img->setAttribute('class', $cls . ($cls?' ':'') . 'img-responsive');
            
            $alt = $img->getAttribute('alt');
            $img->setAttribute('alt', $alt ? $alt : '#');
        }
    }
    
    static function _fixEmbed($doc){
        $replaces = [];
        
        $elements = ['blockquote', 'video', 'div', 'iframe'];
        foreach($elements as $tag){
            $els = $doc->getElementsByTagName($tag);
            if(!$els->length)
                continue;
            
            for($i=($els->length-1); $i>=0; $i--){
                $el = $els->item($i);
                
                $nodeName = $el->nodeName;
                
                if('blockquote' == $nodeName){
                    $cls = $el->getAttribute('class');
                    if(false === strstr($cls, 'instagram-media') && false === strstr($cls, 'twitter-tweet'))
                        continue;
                }elseif('div' == $nodeName){
                    $cls = $el->getAttribute('class');
                    if(false === strstr($cls, 'fb-video') && false === strstr($cls, 'fb-post'))
                        continue;
                }
                
                $elClone = $el->cloneNode(true);
                
                $tmpDom  = new \DOMDocument;
                $tmpDom->appendChild($tmpDom->importNode($elClone, true));
                
                $elString = $tmpDom->saveHTML();
                
                $elEmbed = new \Formatter\Object\Embed($elString);
                if(!$elEmbed->dom)
                    continue;
                
                $elClone = $doc->importNode($elEmbed->dom, true);
                $el->parentNode->replaceChild($elClone, $el);
            }
        }
    }
    
    static function parseContent(String $html){
        $html = trim($html);
        if(!$html)
            return $html;
        $html = '<!DOCTYPE html><html><body>' . $html . '</body></html>';
        $doc  = \HTML5_Parser::parse($html);
        
        self::_fixImage($doc);
        self::_fixEmbed($doc);
        $html = $doc->saveHTML();
        
        preg_match('!^.+<body>(.+)</body>.+$!s', $html, $m);
        
        return $m[1];
    }
}