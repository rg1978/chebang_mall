<?php

//use Barryvdh\Debugbar\Support\Clockwork\Converter;
use DebugBar\OpenHandler;

class debugbar_ctl_openHandlerController 
{
   
    public function handle()
    {
        $debugbar = debugbar::instance();

        if (!$debugbar->isEnabled()) {
            abort('500', 'Debugbar is not enabled');
        }

        $openHandler = new debugbar_openHandler($debugbar, 'OnexDebugBar');

        $data = $openHandler->handle(null, false, false);
        //logger::emergency('kkk', $data);

        //        return response::json($data);
        return response::make(
            $data, 200, array(
                'Content-Type' => 'application/json'
            )
        );
    }

    /**
     * Return Clockwork output
     *
     * @param $id
     * @return mixed
     * @throws \DebugBar\DebugBarException
     */
    public function clockwork($id)
    {
        $request = [
            'op' => 'get',
            'id' => $id,
        ];

        $debugbar = $this->debugbar;

        if (!$debugbar->isEnabled()) {
            $this->app->abort('500', 'Debugbar is not enabled');
        }

        $openHandler = new OpenHandler($debugbar);

        $data = $openHandler->handle($request, false, false);

        // Convert to Clockwork
        $converter = new Converter();
        $output = $converter->convert(json_decode($data, true));

        return response::json($output);
    }
}
