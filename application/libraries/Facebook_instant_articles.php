<?php defined('BASEPATH') OR exit('No direct script access allowed');

include_once('vendor/autoload.php');
/*
*
* Facebook instant articles library for codeigniter.
* You have to instal facebook instat articles php sdk via composer that way you get the autoloader path for your project
* But since codeigniter in LAMP always the same you can just use the folder the library with the "vendor/autoload.php" file 
* 
* I must say this library does not include the client part from the instant article api becouse my app does not need ir
* I find it to very much more simple to just create the code and copy and paste to your FB page administrator, if you want
* you can contribute to this proyect with that part.
*
* If i would have found something like this i would have saved 2 weeks of learning how to use it
* Made by Gadiel Blancas with love <3 - gadiel.blancas@gmail.com
*/
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\Time;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\Video;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\Sponsor;
use Facebook\InstantArticles\Elements\Paragraph;
use Facebook\InstantArticles\Elements\Slideshow;
use Facebook\InstantArticles\Elements\Cite;
use Facebook\InstantArticles\Transformer\Transformer;

use Facebook\InstantArticles\Transformer\Warnings\InvalidSelector;
use Facebook\InstantArticles\Transformer\Getters\JSONGetter;

use Facebook\InstantArticles\Validators\InstantArticleValidator;
use Facebook\InstantArticles\Client\Client;
use Facebook\InstantArticles\Client\Helper;
use Facebook\InstantArticles\Validators\Type;
use Facebook\Facebook;
Class Facebook_instant_articles
{

	private $instantArticle;
	private $rules; 
	private $charset; 

	public function __construct(){
        
        //this code is necesary to avoid printing unwanted debug messages such as ========= DEBUG =====
        //took me a while to figure out
        \Logger::configure(
            [
                'rootLogger' => [
                    'appenders' => ['facebook-instantarticles-transformer']
                ],
                'appenders' => [
                    'facebook-instantarticles-transformer' => [
                        'class' => 'LoggerAppenderConsole',
                        'threshold' => 'INFO',
                        'layout' => [
                            'class' => 'LoggerLayoutSimple'
                        ]
                    ]
                ]
            ]
        );

        $this->instantArticle = InstantArticle::create();

        //this is how you load your json rules placed in the same library folder
        $this->rules = file_get_contents( dirname(__FILE__) . "/inst_art_transformer_custom_rules.json", true);

        $this->charset = 'UTF-8';

	}
    

    #render the article and return the reslt html
	public function renderInstantArticle(){

	    $render = $this->instantArticle->render('<!doctype html>'."\n", true)."\n";
        return $render;
	}
    
    #the canonical url is the url of your alrticle in your website (web-version) that should exist and already publish an 
    #visible for everyone
	public function setCanonicalUrl($url){

    	$this->instantArticle
    	    ->withCanonicalUrl($url);

	}
   
    #the style you set in your facebook profile you can have multiple the basic ones if "default"
	public function setStyle($style){
       $this->instantArticle
           ->withStyle( $style );
	}

	#footer string
	public function setFooter($footer){

		$this->instantArticle
		   ->withFooter(
              Footer::create()
                  ->withCredits($footer)
           );
	}

	#how to create an add with your url height and width urls shoul be like "http://www.youradserver.com/whatever123213123"
	public function create_add($add_url){

		$this->instantArticle
		   ->addChild(
            Ad::create()
                ->withSource($add_url)
                ->withHeight(250)
                ->withWidth(300)
            );
	}

	#how yo create the header part of your article depending on what you need, i coment some parts because i do not need them
	public function setHeader($mainTitle, $subtitle, $publishedDate, $modifiedDate, $autorName, $cover){

		$this->instantArticle
	        ->withHeader(
	            Header::create()
	                ->withTitle($mainTitle)
	                //->withSubTitle($subtitle)
	                ->withPublishTime(
	                    Time::create(Time::PUBLISHED)
	                        ->withDatetime(
	                            \DateTime::createFromFormat(
	                                'j-M-Y G:i:s',
	                                $publishedDate
	                            )
	                        )
	                )
	                ->withModifyTime(
	                    Time::create(Time::MODIFIED)
	                        ->withDatetime(
	                            \DateTime::createFromFormat(
	                                'j-M-Y G:i:s',
	                                $modifiedDate
	                            )
	                        )
	                )
	                ->addAuthor(
	                    Author::create()
	                        ->withName($autorName)
	                        //->withDescription('Author more detailed description')
	                )
	                //->withKicker('Some kicker of this article')
	                ->withCover(
	                    Image::create()
	                        ->withURL($cover)
	                        ->withCaption(
	                            Caption::create()
	                                ->appendText($mainTitle)
	                        )
	                )

	        );	
	}
   
    
    #this is where the article part of your body is created transforming tags and iframes that you have check json rules
	public function build_body($arrBodyElements){


			$transformer = new Transformer();

			$transformer->loadRules($this->rules);


			// Get errors from transformer
			$warnings = $transformer->getWarnings();

			$transformer->transformString( $this->instantArticle, $arrBodyElements, $this->charset );


   }


   #for google analitics :)
   public function apend_code($code){
		
		$this->instantArticle
	        ->addChild(
	            Analytics::create()
	                ->withHTML($code)
	        );

   }
	

    #this is an example i took from facebook website and is very usefull to create elements from cero
	public function element(){
             
            $document = new \DOMDocument();

			$fragment = $document->createDocumentFragment();
			$fragment->appendXML(
			    '<h1>Some custom code</h1>'.
			    '<script>alert("test");</script>'
			);

			$article =
			    InstantArticle::create()
			        ->withCanonicalUrl('http://foo.com/article.html')
			        ->withHeader(
			            Header::create()
			                ->withTitle('Big Top Title')
			                ->withSubTitle('Smaller SubTitle')
			                ->withPublishTime(
			                    Time::create(Time::PUBLISHED)
			                        ->withDatetime(
			                            \DateTime::createFromFormat(
			                                'j-M-Y G:i:s',
			                                '14-Aug-1984 19:30:00'
			                            )
			                        )
			                )
			                ->withModifyTime(
			                    Time::create(Time::MODIFIED)
			                        ->withDatetime(
			                            \DateTime::createFromFormat(
			                                'j-M-Y G:i:s',
			                                '10-Feb-2016 10:00:00'
			                            )
			                        )
			                )
			                ->addAuthor(
			                    Author::create()
			                        ->withName('Author Name')
			                        ->withDescription('Author more detailed description')
			                )
			                ->addAuthor(
			                    Author::create()
			                        ->withName('Author in FB')
			                        ->withDescription('Author user in facebook')
			                        ->withURL('http://facebook.com/author')
			                )
			                ->withKicker('Some kicker of this article')
			                ->withCover(
			                    Image::create()
			                        ->withURL('https://jpeg.org/images/jpegls-home.jpg')
			                        ->withCaption(
			                            Caption::create()
			                                ->appendText('Some caption to the image')
			                        )
			                )
			                ->withSponsor(
			                    Sponsor::create()
			                        ->withPageUrl('http://facebook.com/my-sponsor')
			                )
			        )
			        // Paragraph1
			        ->addChild(
			            Paragraph::create()
			                ->appendText('Some text to be within a paragraph for testing.')
			        )
			        // Paragraph2
			        ->addChild(
			            Paragraph::create()
			                ->appendText('Other text to be within a second paragraph for testing.')
			        )
			        // Slideshow
			        ->addChild(
			            SlideShow::create()
			                ->addImage(
			                    Image::create()
			                        ->withURL('https://jpeg.org/images/jpegls-home.jpg')
			                )
			                ->addImage(
			                    Image::create()
			                        ->withURL('https://jpeg.org/images/jpegls-home2.jpg')
			                )
			                ->addImage(
			                    Image::create()
			                        ->withURL('https://jpeg.org/images/jpegls-home3.jpg')
			                )
			        )
			        // Paragraph3
			        ->addChild(
			            Paragraph::create()
			                ->appendText('Some text to be within a paragraph for testing.')
			        )
			        // Ad
			        ->addChild(
			            Ad::create()
			                ->withSource('http://foo.com')
			        )
			        // Paragraph4
			        ->addChild(
			            Paragraph::create()
			                ->appendText('Other text to be within a second paragraph for testing.')
			        )
			        // Analytics
			        ->addChild(
			            Analytics::create()
			                ->withHTML($fragment)
			        )
			        // Footer
			        ->withFooter(
			            Footer::create()
			                ->withCredits('Some plaintext credits.')
			        );


                    $render = $article->render('<!doctype html>');
                    echo $render;
			        echo htmlentities(  $render );

			        
	}
	
	
	
}