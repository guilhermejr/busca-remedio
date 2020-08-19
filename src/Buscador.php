<?php

namespace BuscaRemedio;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class Buscador
{

    private $crawlerRemedio;
    private $crawlerBula;
    private $erro = false;
    private $codigoBarra;

    public function __construct(string $codigoBarra)
    {
        try {
            
            $browser = new HttpBrowser(HttpClient::create());
            $this->crawlerRemedio = $browser->request('GET', 'https://consultaremedios.com.br');
            $form = $this->crawlerRemedio->filter('.search-autocomplete__form')->form(); 
            $form['termo'] = $codigoBarra;
            $this->crawlerRemedio = $browser->submit($form);
            $this->crawlerBula = $browser->request('GET', substr($this->crawlerRemedio->getUri(), 0, -1) . 'bula');
            $this->codigoBarra = $codigoBarra;
 
        } catch (\Exception $e) {
            $this->erro = true;
        }
        
    }

    public function getJSON(bool $cli = false) : string
    {
        if ($cli) {
            return json_encode($this->getArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($this->getArray());
        }
    }

    public function getArray() : array
    {
        $remedio = [];

        if ($this->erro) {
            $remedio['retorno'] = false;
        } else {
            $remedio['retorno'] = true;
            $remedio['codigoBarra'] = $this->codigoBarra;
            $remedio['nome'] = $this->getNome();
            $remedio['paraQueServe'] = $this->getParaQueServe();
            $remedio['comoUsar'] = $this->getComoUsar();
            $remedio['contraIndicacao'] = $this->getContraIndicacao();
            $remedio['reacoesAdversas'] = $this->getReacoesAdversas();
            $remedio['armazenagem'] = $this->getArmazenagem();
            $remedio['bula'] = $this->getBula();

            if (is_null($remedio['nome'])) {
                $remedio['retorno'] = false;
            }
        }
        
        return $remedio;

    }

    private function getArmazenagem() 
    {
        if ($this->crawlerRemedio->filter('#storage_care-collapse')->count()) {
            return trim($this->crawlerRemedio->filter('#storage_care-collapse')->html());
        }
        return null;
    }

    private function getReacoesAdversas() 
    {
        if ($this->crawlerRemedio->filter('#adverse_reactions-collapse > div')->count()) {
            return trim($this->crawlerRemedio->filter('#adverse_reactions-collapse > div')->html());
        }
        return null;
    }

    private function getContraIndicacao() 
    {
        if ($this->crawlerRemedio->filter('#contraindication-collapse > div')->count()) {
            return trim($this->crawlerRemedio->filter('#contraindication-collapse > div')->html());
        }
        return null;
    }

    private function getComoUsar() 
    {
        if ($this->crawlerRemedio->filter('#dosage-collapse > div')->count()) {
            return trim($this->crawlerRemedio->filter('#dosage-collapse > div')->html());
        }
        return null;
    }

    private function getParaQueServe() 
    {
        if ($this->crawlerRemedio->filter('#indication-collapse > div > p:nth-child(1)')->count()) {
            return strip_tags(trim($this->crawlerRemedio->filter('#indication-collapse > div > p:nth-child(1)')->html()));
        }
        return null;
    }

    private function getNome() 
    {
        if ($this->crawlerRemedio->filter('.product-presentation__option-description')->count()) {
            return strip_tags(trim($this->crawlerRemedio->filter('.product-presentation__option-description')->html()));
        }
        return null;
    }

    private function getBula()
    {
        if ($this->crawlerBula->filter('div.leaflet-content.col-xs-12.col-sm-8.col-sm-offset-2.marginTop-20')->count()) {
            return strip_tags(trim($this->crawlerBula->filter('div.leaflet-content.col-xs-12.col-sm-8.col-sm-offset-2.marginTop-20')->html()));
        }
        return null;
    }

}