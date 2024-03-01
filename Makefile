### ANALYSIS ###

phpstan:
	composer phpstan

ccs:
	composer ccs

fcs:
	composer fcs

ci:
	composer ci

play:
	php bin/console app:game:play
