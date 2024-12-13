<?php

interface ArchiveInterface {

  public function getAllArchivedItems();

  public function retrieveArchivedItem($itemId);

  public function deleteArchivedItem($itemId);
}