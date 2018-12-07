Required software:
- macOS 10.12.6 or later
- MAMP 4.5 (3208)
- PHP 7.2.1
- Apache
- MySQL 5.6.38
- R 3.5.1
	- Install "caret" and "rjson" packages with dependencies=TRUE
- Chrome 68
- Python 2.7

Newer versions (or slightly older) of any of these software (except Python 3 may be different) should work.

Adding a new game to this project checklist:
- Add model entry to model.json, following structure of other games
- Ctrl+F "if ($game ===" in responsePage.php and fill in extraction of features
- Ensure $basicInfoAll keys match model
- Have functional logging in the game itself (ensuring the bug with logger scope is fixed)
