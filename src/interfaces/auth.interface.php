<?php

interface AuthInterface {

  public function userExists($username);

  public function createUser($full_name, $username, $password, $role = null);

  public function login($username, $password);

  public function createSession($user_id, $full_name);

  public function blacklistToken($token);

  public function endSession($user_id);

  public function getDecodedJWT();

  public function hasRole($role);

  public function checkRoleAccess($requiredRole);
}