<?php 

class debugbar_ctl_assetController 
{
    /**
     * Return the javascript for the Debugbar
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function js()
    {
        $debugbar = debugbar::instance();        

        $renderer = $debugbar->getJavascriptRenderer();

        $content = $renderer->dumpAssetsToString('js');

        $response = response::make($content, 200, array(
            'Content-Type' => 'text/javascript',
        ));

        return $this->cacheResponse($response);
    }

    /**
     * Return the stylesheets for the Debugbar
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function css()
    {
        $debugbar = debugbar::instance();
        
        $renderer = $debugbar->getJavascriptRenderer();

        $content = $renderer->dumpAssetsToString('css');

        $response = response::make($content, 200, array(
            'Content-Type' => 'text/css',
        ));

        return $this->cacheResponse($response);
    }

    /**
     * Cache the response 1 year (31536000 sec)
     */
    protected function cacheResponse($response)
    {
        $response->setSharedMaxAge(31536000);
        $response->setMaxAge(31536000);
        $response->setExpires(new \DateTime('+1 year'));

        return $response;
    }
}
