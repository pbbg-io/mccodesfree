<?php
/*
MCCodes FREE
battletent.php Rev 1.1.0
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
if ($ir['location'] != 4)
{
    print
            "You cannot challenge the Battle Tent because it is in the Industrial Sector.";
    $h->endpage();
    die("");
}
$bots = array();
$moneys = array();
print
        "<h3>Battle Tent</h3>
<b>Welcome to the battle tent! Here you can challenge NPCs for money.</b>
<table width=75%><tr style='background: gray; '><th>Bot Name</th><th>Level</th><th>Times Owned</th><th>Ready To Be Challenged?</th><th>Money Won</th><th>Challenge</th></tr>";
foreach ($bots as $k => $v)
{
    $earn = $moneys[$k];
    $q =
            mysql_query(
                    "SELECT u.*,c.npcid FROM users u LEFT JOIN challengesbeaten c ON c.npcid=u.userid AND c.userid=$userid  WHERE u.userid=$v",
                    $c);
    $r = mysql_fetch_array($q);
    $q =
            mysql_query(
                    "SELECT count(*) FROM challengesbeaten WHERE npcid=$v",
                    $c);
    $times = mysql_result($q, 0, 0);
    print
            "<tr><td>{$r['username']}</td><td>{$r['level']}</td><td>$times</td><td>";
    if ($r['hp'] >= $r['maxhp'] / 2)
    {
        print "<font color=green>Yes</font>";
    }
    else
    {
        print "<font color=red>No</font>";
    }
    print "</td><td>$earn</td><td>";
    if ($r['npcid'])
    {
        print "<i>Already</i>";
    }
    else
    {
        print "<a href='attack.php?ID={$r['userid']}'>Challenge</a>";
    }
    print "</td></tr>";
}
print "</table>";
$h->endpage();
