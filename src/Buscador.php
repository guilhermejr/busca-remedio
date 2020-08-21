<?php

namespace BuscaRemedio;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class Buscador
{

    private $driver;
    private $erro = false;
    private $codigoBarra;

    public function __construct(string $codigoBarra)
    {

        try {
            
            $host = 'http://localhost:4444/wd/hub';
            $desiredCapabilities = DesiredCapabilities::chrome();
            $desiredCapabilities->setCapability('acceptSslCerts', false);
            $desiredCapabilities->setCapability('moz:firefoxOptions', ['args' => ['-headless']]);
            $this->driver = RemoteWebDriver::create($host, $desiredCapabilities);
            $this->driver->get('https://consultaremedios.com.br/');
            $this->driver->findElement(WebDriverBy::name('termo'))->sendKeys($codigoBarra)->submit();
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

            if (is_null($remedio['nome'])) {
                $remedio['retorno'] = false;
            }
        }
        
        //$this->driver->quit();
        return $remedio;

    }

    private function getArmazenagem() 
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.storage_care-link'))->click();
        if ($this->driver->findElement(WebDriverBy::cssSelector('#storage_care-collapse div'))->getText()) {
            return $this->driver->findElement(WebDriverBy::cssSelector('#storage_care-collapse div'))->getText();
        }
        return null;
    }

    private function getReacoesAdversas() 
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.adverse_reactions-link'))->click();
        if ($this->driver->findElement(WebDriverBy::cssSelector('#adverse_reactions-collapse > div'))->getText()) {
            return $this->driver->findElement(WebDriverBy::cssSelector('#adverse_reactions-collapse > div'))->getText();
        }
        return null;
    }

    private function getContraIndicacao() 
    {
        if ($this->driver->findElement(WebDriverBy::cssSelector('#contraindication-collapse > div'))->getText()) {
            return $this->driver->findElement(WebDriverBy::cssSelector('#contraindication-collapse > div'))->getText();
        }
        return null;
    }

    private function getComoUsar() 
    {
        $this->driver->findElement(WebDriverBy::cssSelector('.dosage-link.collapsed'))->click();
        if ($this->driver->findElement(WebDriverBy::cssSelector('#dosage-collapse'))->getText()) {
            return $this->driver->findElement(WebDriverBy::cssSelector('#dosage-collapse'))->getText();
        }
        return null;
    }

    private function getParaQueServe() 
    {
        if ($this->driver->findElement(WebDriverBy::cssSelector('#indication-collapse > div > p:nth-child(1)'))->getText()) {
            return $this->driver->findElement(WebDriverBy::cssSelector('#indication-collapse > div > p:nth-child(1)'))->getText();
        }
        return null;
    }

    private function getNome() 
    {
        if ($this->driver->findElement(WebDriverBy::cssSelector('span.product-presentation__option-description'))->getText()) {
            return $this->driver->findElement(WebDriverBy::cssSelector('span.product-presentation__option-description'))->getText();
        }
        return null;
    }

}