<?php

namespace BuscaRemedio;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class Buscador
{
    private $crawler;
    private $erro = false;

    public function __construct(string $codigoBarra)
    {
        try {
            $browser = new HttpBrowser(HttpClient::create());
            $this->crawler = $browser->request('GET', 'https://consultaremedios.com.br');
            $form = $this->crawler->filter('.search-autocomplete__form')->form(); 
            $form['termo'] = $codigoBarra;
            
            // submits the given form
            $this->crawler = $browser->submit($form);
 
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
            $remedio['nome'] = $this->getNome();
            $remedio['paraQueServe'] = $this->getParaQueServe();
            $remedio['comoUsar'] = $this->getComoUsar();
            $remedio['contraIndicacao'] = $this->getContraIndicacao();
            $remedio['reacoesAdversas'] = $this->getReacoesAdversas();
            $remedio['armazenagem'] = $this->getArmazenagem();
        }
        
        return $remedio;

    }

    private function getArmazenagem() 
    {
        if ($this->crawler->filter('#storage_care-collapse')->eq(0)->count()) {
            return trim($this->crawler->filter('#storage_care-collapse')->eq(0)->html());
        }
        return null;
    }

    private function getReacoesAdversas() 
    {
        if ($this->crawler->filter('#adverse_reactions-collapse > div')->eq(0)->count()) {
            return trim($this->crawler->filter('#adverse_reactions-collapse > div')->eq(0)->html());
        }
        return null;
    }

    private function getContraIndicacao() 
    {
        if ($this->crawler->filter('#contraindication-collapse > div')->eq(0)->count()) {
            return trim($this->crawler->filter('#contraindication-collapse > div')->eq(0)->html());
        }
        return null;
    }

    private function getComoUsar() 
    {
        if ($this->crawler->filter('#dosage-collapse > div')->eq(0)->count()) {
            return trim($this->crawler->filter('#dosage-collapse > div')->eq(0)->html());
        }
        return null;
    }

    private function getParaQueServe() 
    {
        if ($this->crawler->filter('#indication-collapse > div > p:nth-child(1)')->eq(0)->count()) {
            return strip_tags(trim($this->crawler->filter('#indication-collapse > div > p:nth-child(1)')->eq(0)->html()));
        }
        return null;
    }

    private function getNome() 
    {
        if ($this->crawler->filter('.product-presentation__option-description')->eq(0)->count()) {
            return strip_tags(trim($this->crawler->filter('.product-presentation__option-description')->eq(0)->html()));
        }
        return null;
    }

}