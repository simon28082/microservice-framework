<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-11 15:26
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Console;

use Illuminate\Console\Application as BaseApplication;
use Illuminate\Contracts\Console\Application as ApplicationContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class Application.
 */
class Application extends BaseApplication implements ApplicationContract
{
    public function __construct(Container $laravel, Dispatcher $events, string $version)
    {
        parent::__construct($laravel, $events, $version);
        $this->setName($this->logo());
    }

    /**
     * @return string
     */
    protected function logo(): string
    {
        return <<<str
                                    ____               
                                  ,'  , `.             
           __  ,-.             ,-+-,.' _ |             
         ,' ,'/ /|          ,-+-. ;   , ||  .--.--.    
   ,---. '  | |' | ,---.   ,--.'|'   |  || /  /    '   
  /     \|  |   ,'/     \ |   |  ,', |  |,|  :  /`./   
 /    / ''  :  / /    / ' |   | /  | |--' |  :  ;_     
.    ' / |  | ' .    ' /  |   : |  | ,     \  \    `.  
'   ; :__;  : | '   ; :__ |   : |  |/       `----.   \ 
'   | '.'|  , ; '   | '.'||   | |`-'       /  /`--'  / 
|   :    :---'  |   :    :|   ;/          '--'.     /  
 \   \  /        \   \  / '---'             `--'---'   
  `----'          `----'                               

Microservice Framework(Based on Laravel)
Version:
str;
    }
}
