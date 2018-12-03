# prtg-opcache-status
A small php script which is called from PRTG to collect the status / performance of the php OpCache / APCu for web applications.
It returns multiple channels of data:

- Memory Limit
- Memory Free
- Memory Used
- Cache Entry Count
- Average Request Rate /s
- Average Hit Rate /s
- Average Miss Rate /s
- Average Insert Rate /s
- Current Request Rate /s
- Current Hit Rate /s
- Current Miss Rate /s
- Current Insert Rate /s

### Requirements

* [PRTG] 
* [APC] - APC must be installed an activated

### Installation

Just copy up the script to a publically accessable web location.  
I've committed into the application repository so it goes 

with automatically 
with code depoloyments and lands on the entire fleet of servers.

In PRTG - when you have more than 1 application(php) server, you should be monitoring each individually. 
I have this sensor activated for each php server in each fleet.

Create a new [HTTP Data Advanced] sensor in PRTG and point it at the URL where it can reach the script.

### License
The Unlicense


   [PRTG]: <https://www.paessler.com/prtg>
   [APC]: <http://pecl.php.net/package/APCu>
   [HTTP Data Advanced]: https://www.paessler.com/manuals/prtg/custom_sensors#advanced_sensors
