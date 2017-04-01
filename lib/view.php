<?php

  class View {

    public static function make($view, $content = array()) {
      // Alustetaan Twig
      $twig = self::get_twig();

      $url_base = "";
      $url_format = "";
      $url_default = array();

      $twig->addFunction(new Twig_SimpleFunction('url_format', function($base, $format, $default) use (&$url_base, &$url_format, &$url_default) {
        $url_base = $base;
        $url_format = $format;
        $url_default = $default;
      }));

      $twig->addFunction(new Twig_SimpleFunction('url', function($changes) use (&$url_base, &$url_format, &$url_default) {
        $generator = new UrlGenerator($url_default, $url_format);
        return $url_base ."/". $generator->generate($changes);
      }));

      try{
        // Asetetaan uudelleenohjauksen yhteydessä lisätty viesti
        self::set_flash_message($content);

        // Asetetaan näkymään base_path-muuttuja index.php:ssa määritellyllä BASE_PATH vakiolla
        $content['base_path'] = BASE_PATH;

        // Asetetaan näkymään kirjautunut käyttäjä, jos get_user_logged_in-metodi on toteutettu
        if(method_exists('BaseController', 'getLoggedInUser')) {
          $content['user'] = BaseController::getLoggedInUser();
        }

        // Tulostetaan Twig:n renderöimä näkymä
        echo $twig->render($view, $content);
      } catch (Exception $e){
        die('Virhe näkymän näyttämisessä: ' . $e->getMessage());
      }

      exit();
    }

    private static function get_twig(){
      Twig_Autoloader::register();

      $twig_loader = new Twig_Loader_Filesystem('app/views');

      return new Twig_Environment($twig_loader);
    }

    private static function set_flash_message(&$content) {
      if(isset($_SESSION['flash_message'])){

        $flash = json_decode($_SESSION['flash_message']);

        foreach($flash as $key => $value){
          $content[$key] = $value;
        }

        unset($_SESSION['flash_message']);
      }
    }

  }
