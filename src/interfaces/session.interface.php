<?php

interface SessionInterface {

  public function getSessionsByUserId($user_id);

  public function getSessionsByFullName($full_name);

  public function getAllSessions();

}