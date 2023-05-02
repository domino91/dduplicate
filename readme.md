# What is? #
Simple project for detect duplicate pictures on Qnap server (actually every  Samba shares / Windows shares).

## How it's working? ##
1. First, you should run composer install && bin/console d:s:u, set env APP_USERNAME, APP_WORKGROUP, APP_PASSWORD, APP_HOST, APP_SHARE (the name of the resource where you keep the photos) and APP_DEFAULT_DIR.
2. Second, add tasks to analyze. This cause to fetch a list of root directories on Samba / Windows Share and for every one is creating a new job in queue.
> bin/console app:job:scan
3. Third Run workers.
> bin/console messenger:consume
4. Finally, for display duplicate run:
> bin/console app:analyze:unique

Happy to help ;-)