<?php
/**
 * Dev 2 Posts Shortcode Class
 *
 * @since 1.0
 * @package Dev 2 Posts Plugin
 * @author Pixium Digital Pte Ltd
 */
 
class Dev2Posts_Shortcode
{
    function __construct() {

        add_shortcode( 'dev_to_last' , array( $this, 'render_last_dev_to_articles'));
        add_shortcode( 'dev_to' , array ( $this, 'render_dev_to_articles'));
    }


    /**
     * Get articles with API call to Dev.TO
     *
     * @since 1.0
     */
    function get_articles(){
        $crl = null;
        if(get_option('dev-to-type')=='organization'){
            $crl = curl_init("https://dev.to/api/organizations/".get_option('dev-to-username')."/articles");
        }else if(get_option('dev-to-type')=='me'){
            $crl = curl_init("https://dev.to/api/articles/me");
        }
    
        if($crl){
            curl_setopt($crl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'api-key: ' . get_option ( 'dev-to-api-key' )
                ]
            );
            // curl_setopt($crl, CURLOPT_VERBOSE, false );
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($crl);
            $json = json_decode($result);
            curl_close($crl);
            return $json;
        }
        return null;
    }

    /**
     * Get a single article
     *
     * @since 1.0
     */
    function get_article($id){
        $crl = curl_init("https://dev.to/api/articles/$id");
    
        if($crl){
            curl_setopt($crl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'api-key: ' . get_option ( 'dev-to-api-key' )
                ]
            );
            // curl_setopt($crl, CURLOPT_VERBOSE, false );
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($crl);
            $json = json_decode($result);
            curl_close($crl);
            return $json;
        }
        return null;
    }

    
    /**
     * Shortocde to display recent articles
     *
     * @since 1.0
     */
    function render_last_dev_to_articles($atts) {
	// set up default parameters
        $param = shortcode_atts(array(
            'limit' => '3'
        ), $atts);
        $count = 0;
        if (get_option('dev-to-api-key')) {
            $json = $this->get_articles();
            if($json){
                $render_str = '<link rel="stylesheet" href="'.PLUGIN_ROOT_URL.'/style.css?'.date('l jS \of F Y h:i:s A').'">';
                $render_str .= '<div class="row">';
                foreach($json as $article){
                    $render_str .= $this->render_single_article($article);
                    $count++;
                    if($count >= $param["limit"]) {
                        break;
                    }
                }
                $render_str .= '</div>';
                return $render_str;
            } else {
                return "ERROR";
            }  
        } 
    }


    /**
     * Shortocde to display all articles
     *
     * @since 1.0
     */
    function render_dev_to_articles() {
        if (get_option('dev-to-api-key')) {
            $json = $this->get_articles();
            // handle curl error
            if ($json) {
                $render_str = '<link rel="stylesheet" href="'.PLUGIN_ROOT_URL.'/style.css?'.date('l jS \of F Y h:i:s A').'">';
                $render_str .= '<div class="row card-wrapper">';
                foreach($json as $article) {
                    $render_str .= $this->render_single_article($article);
                }
                $render_str .= '</div>';
                return $render_str;
            } else {
                return "ERROR";
            }
            
        }
    }

    /**
     * Render the article CARD
     */
    function render_single_article($article){
        $timestr = strtotime($article->published_at);
        $organization = ($article && $article->organization) ? $article->organization->name : '-';
        $author = ($article && $article->organization) ? $article->user->name : '-';

        //Get estimated read time
        $id = $article->id;
        $id_article = $this->get_article($id);
        $read_time = $this->get_estimated_time($id_article->body_markdown);

        return  '<div class="card">'.
                    '<div class="cover-img-container">'.
                        '<img class="cover-img" src="'.$article->cover_image.'"/>'.
                    '</div>'.
                    '<div class="content">'.
                        '<div class="author-container">'.
                            '<span class="author-img"><img class="org-image" src="'.$article->organization->profile_image_90.'"></span>'.
                            '<span class="author"><a>'.$author.'</a> from <a>'.$organization.'</a></span>'.
                        '</div>'.
                        '<h1 class="title"><a href="'.$article->url.'" target="_blank">'.$article->title.'</a></h1>'.
                        '<p class="excerpt">'.$article->description.'</p>'.
                        '<div class="bottom-container">'.
                            '<div class="date-wrapper>'.
                                '<span class="day">'.date('d', $timestr).' </span>'.
                                '<span class="month">'.date('M', $timestr).' </span>'.
                                '<span class="year">'.date('Y', $timestr).'</span>'.
                            '</div>'.
                            '<span class="center-dot">&centerdot;</span>'.
                            '<span>'.$read_time.' min read</span>'.
                        '</div>'.
                    '</div>'.
                '</div>';
        // return '<div class="example-1 card">'.
        //             '<div class="wrapper" style="background: url('.$article->cover_image.') center / cover no-repeat;">'.
        //                 '<div class="date">'.
        //                     '<span class="day">'.date('d', $timestr).'</span>'.
        //                     '<span class="month">'.date('M', $timestr).'</span>'.
        //                     '<span class="year">'.date('Y', $timestr).'</span>'.
        //                 '</div>'.
        //                 '<div class="data">'.
        //                     '<div class="content">'.
        //                         '<span class="author">'.$author.'</span>'.
        //                         '<h1 class="title"><a href="'.$article->url.'" target="_blank">'.$article->title.'</a></h1>'.
        //                     '</div>'.
        //                     '<input type="checkbox" id="show-menu" />'.
        //                     '<ul class="menu-content">'.
        //                         '<li>'.
        //                             '<a href="#" class="fa fa-bookmark-o"></a>'.
        //                         '</li>'.
        //                         '<li><a href="#" class="fa fa-heart-o"><span>47</span></a></li>'.
        //                         '<li><a href="#" class="fa fa-comment-o"><span>8</span></a></li>'.
        //                     '</ul>'.
        //                 '</div>'.
        //             '</div>'.
        //         '</div>';
    }

    /**
     * Get esitimated reading time of card
     */
    function get_estimated_time($content, $wpm = 275) {
        $word_count = str_word_count(strip_tags($content));
        $read_time = ceil($word_count / $wpm);

        return $read_time;
    }
}
new Dev2Posts_Shortcode();