<?php

# Sorry about the OIDs but there doesn't seem to be a matching MIB available... :-/

$version = "Kernel " . trim(snmp_get($device, "1.3.6.1.4.1.14125.100.1.8.0", "-OQv"),'" ');
$version .= " / Apps " . trim(snmp_get($device, "1.3.6.1.4.1.14125.100.1.9.0", "-OQv"),'" ');

$serial = trim(snmp_get($device, "1.3.6.1.4.1.14125.100.1.7.0", "-OQv"),'" ');

# There doesn't seem to be a real hardware identification.. sysName will have to do?
$hardware = str_replace("EnGenius ","",snmp_get($device,"sysName.0", "-OQv")) . " v" . trim(snmp_get($device, "1.3.6.1.4.1.14125.100.1.6.0", "-OQv"),'" .');

$mode = snmp_get($device, "1.3.6.1.4.1.14125.100.1.4.0", "-OQv");

switch ($mode)
{
  case 0:
    $features = "Router mode";
    break;
  case 1:
    $features = "Universal repeater mode";
    break;
  case 2:
    $features = "Access Point mode";
    break;
  case 3:
    $features = "Client Bridge mode";
    break;
  case 4:
    $features = "Client router mode";
    break;
  case 5:
    $features = "WDS Bridge mode";
    break;
}

?>
