<?php
class Tags
{
  public static function getAll()
  {
    $stmt = DB::run("SELECT * FROM `tags` GROUP BY `name`")->fetchAll();
    return $stmt;
  }

  public static function getByTorrentId($id)
  {
    $stmt = DB::run("SELECT * FROM `tags` wHERE torrentid = ?", [$id])->fetchAll();
    return $stmt;
  }
}