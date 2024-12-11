<?php

interface ResponseInterface{
  
  public function responsePayload($payload, $remarks, $message, $code);

  public function notFound();
}