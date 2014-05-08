<?php

class MockLib { function __call($name, $parameters){ return false; } }

class MockDB {
    function limit(){
        return new MockChannelEntries();
    }
}

class MockChannelEntries {

    function get_entries($channel_id)
    {
        return new MockChannelEntriesResult();
    }

    function get_entry($id, $channel)
    {
        return new MockChannelEntriesResult("array", $id);
    }

    function get_where()
    {
        return new MockChannelEntriesResult();
    }

}

class MockChannelEntriesResult {

    function __construct($type = "plain", $id = null) {
        $this->type = $type;
        $this->id = $id;
    }

    function result_array()
    {
        $results = array(
            "1234" => array("entry_id" => 1234, "title" => "Multi Superb", "field_id_46" => "[12][45][56]", "field_id_47" => "3.500" ),
            "2345" => array("entry_id" => 2345, "title" => "Woops", "field_id_46" => "[8][9][10]", "field_id_47" => "5.000" )
        );

        if(isset($this->id)) {
            $results = $results[$this->id];
        }

        return $this->type == "array" ? array($results) : $results;
    }

    function num_rows()
    {

    }

}

class MockEE {

    function __construct() {
        $this->load = new MockLib();
        $this->api = new MockLib();
        $this->api_channel_fields = new MockLib();
        $this->api_channel_entries = new MockLib();
        $this->channel_entries_model = new MockChannelEntries();
        $this->db = new MockDB();
        $this->store = new MockLib();
    }

}

class MockOrder {
    function __construct($items)
    {
        $this->items = $items;
        $this->order_discount = 0;
        $this->order_custom9 = "";
    }
    function save() {

    }
}

class MockItem {
    function __construct($entry_id)
    {
        $this->entry_id = $entry_id; 
    }
}