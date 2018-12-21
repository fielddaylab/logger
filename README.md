This project is the work of a student employee (and briefly another full-time developer) for Field Day Lab (https://fielddaylab.org/). It uses a webpage (HTML/JS) to interface with a server (LAMP/MAMP) to parse (PHP) data (MySQL) from Field Day's free online games (https://theyardgames.org) and analyzes that data using linear and binomial regressions (R, Python) and machine-learning algorithms (Python) to predict player performance using a set of features defined per-game.

Required software:
- macOS 10.12.6
- MAMP 4.5 (3208)
- PHP 7.2.1
- Apache
- MySQL 5.6.38
- R 3.5.1
	- Install "caret" and "rjson" packages with dependencies=TRUE
- Chrome 68
- Python 2.7
	- Install scikit-learn package

Newer versions (or slightly older) of any of these software (except Python 3 may be different) should work.

This project makes use of the following JS libraries automatically included:
- jQuery 3.3.1
- Bootstrap 3.3.7
- Plotly (latest)
- Clipboard.js 2.0.0
- Select2 4.0.6-rc.0 (included but not currently used)
- Open Sans font (weights 400 and 700)

The site was developed for Chrome; Safari has security features that break some features and other browsers are untested. The JavaScript was written (mostly) according to ES6 standards and conventions.

Adding a new game to this project checklist:
- Add model entry to model.json, following structure of other games
- Ctrl+F "if ($game ===" in responsePage.php and fill in extraction of features (much of it can be copy+pasted from another game)
- Ensure $basicInfoAll keys match model
- Have functional logging in the game itself (ensuring the bug with logger scope is fixed)
- Create a new directory in the logger-data directory for the game
- Import data from live server
- Un-disable the option in the #gameSelect element

The high-level flow of this program is
	HTML/JS page loads->
	Requests config.json->
	Populates tables and top of page->
	User selects features, filters, and tables and presses go->
	Sends requests per-column to PHP->
	PHP queries SQL db->
	SQL results are parsed into features->
	Features written to file and executes R/Python commands to run ML algorithms->
	PHP sends results back to JS->
	JS inserts results into tables

Some things to note:
- SQL data only exists from Dec 5 to ~Dec 20 (when it was last uploaded), whereas the live FD server goes back to March (with mostly bad data until Dec)
- Older features like the single tab have been untouched for a long time and are almost certainly no longer working
- When developing (at least for HTML/JS/CSS changes), always keep the Chrome console open and tick "Disable cache" in the Network tab to make sure changes appear
- Aborting requests with the button (which calls jquery's abort method on the jquery XMLHttpRequest object) only stops the client from listening; it does not stop the server from running the calculations. To free server resources, either wait for the aborted requests to finish or manually kill anything mysqld, httpd, R, or Python