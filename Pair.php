<?php

class Pair
{
    protected $endpoint = 'https://api.dexscreener.com/latest/dex/search?q=';
    protected $address;
    protected $has_error = false;

    public function __construct($address)
    {
        $this->address = $address;
    }

    public function info()
    {
        $this->cleanAddress();
        
        if (empty($this->address))
            return false;
            
            $info = json_decode(file_get_contents($this->endpoint . $this->address));

            if ($info && is_object($info) && !empty($info->pairs[0])) {
                return $info->pairs[0];
            } else {
                return false;
            }
    }
    
    public function cleanAddress()
    {
	    $this->address = preg_replace('/[^A-Za-z0-9\-]/', '', $this->address);
    }

}
