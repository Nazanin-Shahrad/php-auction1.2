<?php

use App\Models\Item;

//INCLUDE THE CODE TO REQUIRE THE BOOTSTRAP.PHP HERE
require_once(__DIR__ . "/../app/bootstrap.php");

if(isset($_GET['id'])) {
    $validid = pf_validate_number($_GET['id'], "value", CONFIG_URL);
} else {
    $validid = 0;
}

//INCLUDE THE CODE TO REQUIRE THE HEADER.PHP HERE
require(__DIR__ . "/../app/Layouts/header.php");
if($validid == 0) {
    //If a category id has not been specified, retrieve all items (as an array of objs) that are not expired
    $items = Item::find("date > NOW()");
} else {
    //If a category id has been specified, retrieve all items (as an array of objs) that are not expired and are a member of a the category
    $items = Item::find("date > NOW() AND cat_id = $validid");
}

?>
    <h1>Items available</h1>
    <table cellpadding='5'>
        <tr>
            <th>Image</th>
            <th>Item</th>
            <th>Bids</th>
            <th>Price</th>
            <th>End Date for this Item</th>
        </tr>

<?php
if(!$items) {
    echo "<tr><td colspan=4>No items!</td></tr>";
} else {
//Iterate over each item object
    /* @var $item \App\Models\Item */
    foreach($items as $item) {

        echo "<tr>";

        //Load Image objects into item
        //ADD THE CODE TO LOAD THE IMAGES INTO $item
        $item->getImages();

        //If there are no images, alert user
        if(!$item->get('imageObjs')) {
            echo "<td>No image</td>";
        } else {
            //Return only the first image obj from the array of image objects in the item
            $img = $item->get('imageObjs');
            $firstImg = array_shift($img);
            echo "<td><img src='imgs/" . $firstImg->get('name') . "' width='100'></td>";
        }

        echo "<td>";
        echo "<a href='itemdetails.php?id={$item->get('id')}'>{$item->get('name')}</a>";

        //If the user is logged in, check to see if the current logged in user is the owner of the item
        if($session->isLoggedIn()) {
            if($session->getUser()->get('id') == $item->get('user_id')) {
                echo " - [<a href='edititem.php?id={$item
				->get('id')}'>edit</a>]";
            }
        }
        echo "</td>";

        echo "<td>";
        //Load Bid objects into item
        $item->getBids();
        //If there are no bid objects, alert user
        if(!$item->get('bidObjs')) {
            echo "0";
        } else {
            //Display the number of bids to the user
            echo count($item->get('bidObjs')) . "</td>";
        }

        echo "<td>" . CONFIG_CURRENCY;
        //If there are no bids for the item, display the items starting price, otherwise display the highest bid
        if(!count($item->get('bidObjs'))) {
            echo sprintf('%.2f', $item->get('price'));
        } else {
            $itemBids = $item->get('bidObjs');
            $highestBid = array_shift($itemBids);
            echo sprintf('%.2f', $highestBid->get('amount'));
        }
        echo "</td>";

        echo "<td>" . date("D jS F Y g.iA", strtotime($item->get('date'))) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";
//INCLUDE THE CODE TO REQUIRE THE FOOTER.PHP HERE
require("../app/Layouts/footer.php");
