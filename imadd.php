<?php
/*
MCCodes FREE
imadd.php Rev 1.1.0
Copyright (C) 2005-2012 Dabomstew

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

session_start();
require "global_func.php";
if ($_SESSION['loggedin'] == 0)
{
    header("Location: login.php");
    exit;
}
$userid = $_SESSION['userid'];
require "header.php";
$h = new headers;
$h->startheaders();
include "mysql.php";
global $c;
$is =
        mysql_query(
                "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid=$userid",
                $c) or die(mysql_error());
$ir = mysql_fetch_array($is);
check_level();
$fm = money_formatter($ir['money']);
$cm = money_formatter($ir['crystals'], '');
$lv = date('F j, Y, g:i a', $ir['laston']);
$h->userdata($ir, $lv, $fm, $cm);
$h->menuarea();
$_GET['ID'] = abs((int) $_GET['ID']);
$_GET['price'] = abs((int) $_GET['price']);
if ($_GET['price'])
{
    $q =
            mysql_query(
                    "SELECT iv.*,i.* FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid WHERE inv_id={$_GET['ID']} and inv_userid=$userid",
                    $c);
    if (mysql_num_rows($q) == 0)
    {
        print "Invalid Item ID";
    }
    else
    {
        $r = mysql_fetch_array($q);
        mysql_query(
                "INSERT INTO itemmarket VALUES(NULL,'{$r['inv_itemid']}',$userid,{$_GET['price']})",
                $c);
        mysql_query(
                "UPDATE inventory SET inv_qty=inv_qty-1 WHERE inv_id={$_GET['ID']}",
                $c);
        mysql_query("DELETE FROM inventory WHERE inv_qty=0", $c);
        mysql_query(
                "INSERT INTO imarketaddlogs VALUES ( '', {$r['inv_itemid']}, {$_GET['price']}, {$r['inv_id']}, $userid, "
                        . time()
                        . ", '{$ir['username']} added a {$r['itmname']} to the itemmarket for \${$_GET['price']}')",
                $c);
        print "Item added to market.";
    }
}
else
{
    $q =
            mysql_query(
                    "SELECT * FROM inventory WHERE inv_id={$_GET['ID']} and inv_userid=$userid",
                    $c);
    if (mysql_num_rows($q) == 0)
    {
        print "Invalid Item ID";
    }
    else
    {
        $r = mysql_fetch_array($q);
        print
                "Adding an item to the item market...
<form action='imadd.php' method='get'>
<input type='hidden' name='ID' value='{$_GET['ID']}' />
Price: \$<input type='text' name='price' value='0' /><br />
<input type='submit' value='Add' /></form>";
    }
}
$h->endpage();
