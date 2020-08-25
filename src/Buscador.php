<?php

namespace BuscaRemedio;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\DomCrawler\Crawler;

class Buscador
{

    private $driver;
    private $erro = false;
    private $codigoBarra;
    private $crawlerRemedio;
    private $crawlerBula;

    public function __construct(string $codigoBarra)
    {

        try {
            
            // --- Acessa a página inicial e fas a busca pelo código de barra ---
            $host = 'http://selenium:4444/wd/hub';
            $desiredCapabilities = DesiredCapabilities::chrome();
            $desiredCapabilities->setCapability('acceptSslCerts', false);
            $desiredCapabilities->setCapability('moz:firefoxOptions', ['args' => ['-headless']]);
            $this->driver = RemoteWebDriver::create($host, $desiredCapabilities);
            $this->driver->get('https://consultaremedios.com.br/');
            $this->driver->findElement(WebDriverBy::name('termo'))->sendKeys($codigoBarra)->submit();

            // --- Crawler da página do remédio ---
            $this->driver->get($this->driver->getCurrentURL());
            $this->crawlerRemedio = new Crawler($this->driver->getPageSource());

            // --- Código de barra ---
            $this->codigoBarra = $codigoBarra;
 
        } catch (\Exception $e) {
            $this->erro = true;
        }
        
    }

    // --- getJSON ------------------------------------------------------------
    public function getJSON(bool $cli = false) : string
    {
        if ($cli) {
            return json_encode($this->getArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($this->getArray());
        }
    }


    // --- getArray -----------------------------------------------------------
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

            if (is_null($remedio['nome'])) {
                $remedio['retorno'] = false;
            }
        }
        
        $this->driver->quit();
        return $remedio;

    }

    // --- getArmazenagem -----------------------------------------------------
    private function getArmazenagem() 
    {
        if ($this->crawlerRemedio->filter('#storage_care-collapse')->count()) {
            return trim($this->crawlerRemedio->filter('#storage_care-collapse')->html());
        }
        return null;
    }

    // --- getReacoesAdversas -------------------------------------------------
    private function getReacoesAdversas() 
    {
        if ($this->crawlerRemedio->filter('#adverse_reactions-collapse > div')->count()) {
            return trim($this->crawlerRemedio->filter('#adverse_reactions-collapse > div')->html());
        }
        return null;
    }

    // --- getContraIndicacao -------------------------------------------------
    private function getContraIndicacao() 
    {
        if ($this->crawlerRemedio->filter('#contraindication-collapse > div')->count()) {
            return trim($this->crawlerRemedio->filter('#contraindication-collapse > div')->html());
        }
        return null;
    }

    // --- getComoUsar --------------------------------------------------------
    private function getComoUsar() 
    {
        if ($this->crawlerRemedio->filter('#dosage-collapse > div')->count()) {
            return trim($this->crawlerRemedio->filter('#dosage-collapse > div')->html());
        }
        return null;
    }

    // --- getParaQueServe ----------------------------------------------------
    private function getParaQueServe() 
    {
        if ($this->crawlerRemedio->filter('#indication-collapse > div > p:nth-child(1)')->count()) {
            return strip_tags(trim($this->crawlerRemedio->filter('#indication-collapse > div > p:nth-child(1)')->html()));
        }
        return null;
    }

    // --- getNome ------------------------------------------------------------
    private function getNome() 
    {
        if ($this->crawlerRemedio->filter('.product-presentation__option-description')->count()) {
            return strip_tags(trim($this->crawlerRemedio->filter('.product-presentation__option-description')->html()));
        }
        return null;
    }

}