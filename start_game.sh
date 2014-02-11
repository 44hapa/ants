#!/usr/bin/env sh
./tools/playgame.py --player_seed 42 --end_wait=0.25 --verbose --log_dir game_logs --turns 100 \
--map_file tools/maps/example/tutorial1.map "$@" \
"php tools/sample_bots/php/TopBot.php" \
"php bots/MyBot.php"
#"php tools/sample_bots/php/TopBot.php"
#"php bots/MyBot.php" \
#"php tools/sample_bots/php/TopBot.php"
#"python tools/sample_bots/python/LeftyBot.py" \
#"python tools/sample_bots/python/HunterBot.py" \
#"python tools/sample_bots/python/GreedyBot.py"
