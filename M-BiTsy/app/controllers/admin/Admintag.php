<?php
class Admintag
{

    public function __construct()
    {
        Auth::user(_SUPERMODERATOR, 2);
    }

    public function index()
    {
        // get the data
        $supertag = Tags::getAll();
    
        // send the data
        $data = [
            'title' => 'Tags',
            'supertag' => $supertag
        ];
    
        // send the data
        View::render('tag/index', $data, 'admin');
    }
  
    public function action()
    {
       $name = Input::get('name');
       //$type = Input::get('type');
  
      if (!empty($_POST['addtag'])) {
        if (!$name) {
          Redirect::autolink(URLROOT . "/admintag", Lang::T("YOU_DID_NOT_ENTER_ANYTHING"));
        }
        DB::insert('tags', ['name' => $name]);
        Redirect::autolink(URLROOT . '/admintag', 'Tag Added');
      
      } elseif (!empty($_POST['delete'])) {
  
        if ($_POST["del"]) {
          if (!@count($_POST["del"])) {
            Redirect::autolink(URLROOT . "/admintag", "Nothing selected to fix.");
          }
            $ids = array_map("intval", $_POST["del"]);
            $ids = implode(",", $ids);
            DB::deleteByIds('tags', 'id', $ids);
            Redirect::autolink(URLROOT . '/admintag', 'Tags Deleted');
          }
        } else {
          Redirect::autolink(URLROOT . '/admintag', 'Strange, try again');
        }
      }

}