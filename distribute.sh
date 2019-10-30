
# sh '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictu-gc-plugin-conference/distribute.sh' &>/dev/null

echo '-- JA --';
echo '----------------------------------------------------------------';
echo 'Distribute GC post type plugin';

# clear the log file
> '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/debug.log'

# copy to temp dir
# rsync -r -a --delete '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictu-gc-plugin-conference/' '/shared-paul-files/Webs/temp/'
rsync -r -a --delete '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictu-gc-plugin-conference/' '/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rf '/shared-paul-files/Webs/temp/.git/'
rm '/shared-paul-files/Webs/temp/.gitignore'
rm '/shared-paul-files/Webs/temp/.config.codekit3'
rm '/shared-paul-files/Webs/temp/config.codekit3'
rm '/shared-paul-files/Webs/temp/distribute.sh'
rm '/shared-paul-files/Webs/temp/README.md'
rm '/shared-paul-files/Webs/temp/LICENSE'
rm '/shared-paul-files/Webs/temp/gulpfile.js'
rm '/shared-paul-files/Webs/temp/yarn.lock'
rm '/shared-paul-files/Webs/temp/package.json'


# --------------------------------------------------------------------------------------------------------------------------------
# Vertalingen --------------------------------------------------------------------------------------------------------------------
# --------------------------------------------------------------------------------------------------------------------------------
# remove the .pot
rm '/shared-paul-files/Webs/temp/languages/ictu-gc-plugin-conference.pot'

# copy files to /wp-content/languages/plugins
rsync -ah '/shared-paul-files/Webs/temp/languages/' '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/languages/plugins/'

# languages erics server
rsync -ah '/shared-paul-files/Webs/temp/languages/' '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/live-dutchlogic/wp-content/languages/plugins/'

# languages Sentia accept
rsync -ah '/shared-paul-files/Webs/temp/languages/' '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/accept/www/wp-content/languages/plugins/'

# languages Sentia live
rsync -ah '/shared-paul-files/Webs/temp/languages/' '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/live/www/wp-content/languages/plugins/'


cd '/shared-paul-files/Webs/temp/'
find . -name ‘*.DS_Store’ -type f -delete

# en een kopietje naar Sentia accept
rsync -r -a --delete '/shared-paul-files/Webs/temp/' '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/accept/www/wp-content/plugins/ictu-gc-plugin-conference/'

# en een kopietje naar Sentia live
rsync -r -a --delete '/shared-paul-files/Webs/temp/' '/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/live/www/wp-content/plugins/ictu-gc-plugin-conference/'

# remove temp dir
#  rm -rf '/shared-paul-files/Webs/temp/'


echo 'Ready';
echo '----------------------------------------------------------------';
