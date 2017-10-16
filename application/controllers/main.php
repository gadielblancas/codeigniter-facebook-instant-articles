<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

	public function index()
	{
       		
	}
    
    function do_instant_article(){

        $this->load->library('Facebook_instant_articles', NULL, 'fbInstantArticles');
        
        #this is an array obtained from database with your article info
        $data = "";
        
        // this are your variables however you want to get them post get database etc.
		$nota_id = $this->input->post('nota_id'); 
		$user_id = $this->input->post('user_id'); 
		$canonical_url = $this->input->post('canonical_url'); 
		$autor = $this->input->post('autor'); 
		$style = $this->input->post('style'); 
		
		// your body html generally it comes from a html text editor where you put images paragraphs iframes etc if you need to modify it before
		// do it as your convenience
        $resultStr = $data['yourhtml'];

        //gettig publishing date
        $publishDate = $data['fecha_publicacion'];
        $publishedDate = date("j-M-Y G:i:s", strtotime($publishDate));
        $modifiedDate = date("j-M-Y G:i:s");

        
        //get url
        $this->fbInstantArticles->setCanonicalUrl( $canonical_url ); 

        //set header and the elements check the library
        $this->fbInstantArticles->setHeader( $data['titulo'], $data['descripcion'], 
        	                                 $publishedDate, 
        	                                 $modifiedDate, 
        	                                 $autor, 
        	                                 'http://drive-futboleno.s3.amazonaws.com/assets/images/tv/imgvideos/'.$data['foto']
        	                                );

        //seting default facebook ia style
        $this->fbInstantArticles->setStyle($style); 
        
        // build ia article <segment> section
        $this->fbInstantArticles->build_body($resultStr);
        
        //create an add
        $intAdsZ300x250 = rand(1,99999999);
        $add_url = "http://yourwebserver.com/rvads/www/delivery/afr.php?zoneid=3&amp;cb=".$intAdsZ300x250;
        $this->fbInstantArticles->create_add($add_url);

        //add google analitics
        $this->fbInstantArticles->apend_code("<script>".
												"var strGoogleSitekey = 'your_google_key_id';".
												  "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
												  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
												  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
												  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');".
												  "ga('create', 'UA-a_number_think_is_secret-1', 'auto');".
												  "ga('send', 'pageview');".
											"</script>");
								        	
        //render
        $instantArticleHtml = $this->fbInstantArticles->renderInstantArticle();
        

        //retur results
        $result = array(
        	'html' => $instantArticleHtml, //the html whose actually going to be used (if you decide to program the client you are going to send this)
        	'code' => htmlentities($instantArticleHtml), //this returns html code to display it as "code" i use a plugin to make it look fancy
        	'array' => $contenArr,
        	'resStr' => $resultStr
        );
      		 

		echo json_encode($result); 
	 
	}
}
/* End of file main.php */
/* Location: ./application/controllers/main.php */