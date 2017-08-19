<?php
require_once("vendor/autoload.php");

$climate = new League\CLImate\CLImate;

if (trim(shell_exec("whoami")) != "root")
{
    $climate->shout("Please run this installer as root.");
    return;
}

$climate->white("Creating sonarpoller user... ");
try {
    if (!file_exists("/home/sonarpoller"))
    {
        execCommand("/usr/sbin/useradd -m -d /home/sonarpoller sonarpoller");
    }
}
catch (RuntimeException $e)
{
    $climate->shout("FAILED!");
    return;
}
$climate->lightGreen("OK!");

$climate->white("Installing to /opt/poller... ");
try {
    if (!file_exists("/opt/poller"))
    {
        execCommand("/bin/mkdir /opt/poller");
    }
    execCommand("/bin/cp -R " . dirname(__FILE__) ."/. /opt/poller/");
    execCommand("/bin/chown -R sonarpoller:sonarpoller /opt/poller/.");
}
catch (RuntimeException $e)
{
    $climate->shout("FAILED!");
    return;
}
$climate->lightGreen("OK!");

$climate->white("Setting up scheduler... ");
try {
    execCommand("/bin/cp " . dirname(__FILE__) . "/sonar_poll_scheduler /etc/cron.d/");
    execCommand("/bin/chmod 0644 /etc/cron.d/sonar_poll_scheduler");
}
catch (RuntimeException $e)
{
    $climate->shout("FAILED!");
    return;
}
$climate->lightGreen("OK!");

// $climate->white("Configuring queue listeners... ");
// try {
//     execCommand("/bin/cp conf/monitrc /etc/monit/");
//     execCommand("/bin/cp " . dirname(__FILE__) . "/conf/default /etc/monit/conf.d/");
//     execCommand("/usr/sbin/service monit reload");
//     sleep(3);
//     execCommand("/usr/bin/monit start defaultQueue");
// }
// catch (RuntimeException $e)
// {
//     $climate->shout("FAILED!");
//     return;
// }
// $climate->lightGreen("OK!");

$climate->white("Configuring SNMP MIBs... ");
try {
    execCommand("/bin/sed -i 's/^mibs :/#mibs :/g' /etc/snmp/snmp.conf");
}
catch (RuntimeException $e)
{
    $climate->shout("FAILED!");
    return;
}
$climate->lightGreen("OK!");

$climate->white("Setting up log file... ");
try {
    execCommand("/usr/bin/touch /var/log/sonar_poller.log");
    execCommand("/bin/chown sonarpoller:sonarpoller /var/log/sonar_poller.log");
    execCommand("/bin/cp conf/sonarpoller /etc/logrotate.d/");
}
catch (RuntimeException $e)
{
    $climate->shout("FAILED!");
    return;
}
$climate->lightGreen("OK!");

/**
 * @param $command
 * @return mixed
 */
function execCommand($command)
{
    exec($command,$output,$returnVar);
    if ($returnVar !== 0)
    {
        throw new RuntimeException("Failed to execute $command");
    }
    return $output;
}