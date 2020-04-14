
# sh '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-conference/distribute.sh' &>/dev/null

echo '-- JA --';
echo '----------------------------------------------------------------';
echo 'Distribute GC post type plugin';

# clear the log file
> '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/debug.log'

# copy to temp dir
# rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-conference/' '/Users/paul/shared-paul-files/Webs/temp/'
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-conference/' '/Users/paul/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp/.git/'
rm -rf '/Users/paul/shared-paul-files/Webs/temp/.idea/'
rm '/Users/paul/shared-paul-files/Webs/temp/.gitignore'
rm '/Users/paul/shared-paul-files/Webs/temp/.gitattributes'
rm '/Users/paul/shared-paul-files/Webs/temp/config.codekit3'
rm '/Users/paul/shared-paul-files/Webs/temp/distribute.sh'
rm '/Users/paul/shared-paul-files/Webs/temp/README.md'
rm '/Users/paul/shared-paul-files/Webs/temp/LICENSE'
rm '/Users/paul/shared-paul-files/Webs/temp/gulpfile.js'
rm '/Users/paul/shared-paul-files/Webs/temp/yarn.lock'
rm '/Users/paul/shared-paul-files/Webs/temp/package.json'
rm '/Users/paul/shared-paul-files/Webs/temp/composer.json'


# --------------------------------------------------------------------------------------------------------------------------------
# Vertalingen --------------------------------------------------------------------------------------------------------------------
# --------------------------------------------------------------------------------------------------------------------------------
# remove the .pot
rm '/Users/paul/shared-paul-files/Webs/temp/languages/ictuwp-plugin-conference.pot'

# copy files to /wp-content/languages/plugins
rsync -ah '/Users/paul/shared-paul-files/Webs/temp/languages/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/languages/plugins/'

# languages erics server
rsync -ah '/Users/paul/shared-paul-files/Webs/temp/languages/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/live-dutchlogic/wp-content/languages/plugins/'

# languages Sentia accept
rsync -ah '/Users/paul/shared-paul-files/Webs/temp/languages/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/accept/www/wp-content/languages/plugins/'

# languages Sentia live
rsync -ah '/Users/paul/shared-paul-files/Webs/temp/languages/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/live/www/wp-content/languages/plugins/'


cd '/Users/paul/shared-paul-files/Webs/temp/'
find . -name ‘*.DS_Store’ -type f -delete

# en een kopietje naar Sentia accept
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/accept/www/wp-content/plugins/ictuwp-plugin-conference/'

# en een kopietje naar Sentia live
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/live/www/wp-content/plugins/ictuwp-plugin-conference/'

# remove temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp/'


echo 'Ready';
echo '----------------------------------------------------------------';
