#!/usr/bin/python

import getopt
import sys
import os.path
import zipfile
import urllib
import urlparse
import shutil
import shlex
import time
from subprocess import call

project_root_path = os.path.dirname(os.path.abspath(__file__))


def run_testsuite():
    os.chdir(project_root_path + '/docker')
    time.sleep(20)
    statement = ('docker-compose ' +
                 'run ' +
                 'behat ' +
                 'bash -c "' +
                 './behat' +
                 '"')
    exitcode = call(shlex.split(statement))
    if exitcode > 0:
        sys.exit(1)


def disable_first_run_wizard():
    os.chdir(project_root_path + '/docker')
    statement = ('docker-compose ' +
                 'run ' +
                 '--user ' +
                 'www-data ' +
                 'apache ' +
                 'bash -c "' +
                 'cd /var/www/shopware && php bin/console sw:firstrunwizard:disable && php bin/console sw:cache:clear' +
                 '"')
    exitcode = call(shlex.split(statement))
    if exitcode > 0:
        sys.exit(1)


def grant_api_access(user):
    os.chdir(project_root_path + '/docker')
    vars = {'apiKey': get_api_key(), 'username': user}
    statement = ('docker-compose ' +
                 'run ' +
                 '--user ' +
                 'www-data ' +
                 'apache ' +
                 'bash -c "' +
                 'mysql -u root -ptoor -h mysql -e ' +
                 ' \'UPDATE `s_core_auth` SET `apiKey`=\\"{apiKey}\\" WHERE `username`=\\"{username}\\";\' ' +
                 'shopware"').format(**vars)
    exitcode = call(shlex.split(statement))
    if exitcode > 0:
        sys.exit(1)


def get_api_key():
    with open(project_root_path + '/tests/.env', "r") as cfg:
        for line in cfg:
            if line.startswith('api_key='):
                return line.split('=')[1]
    print 'Error: No api_key defined in tests/.env'
    sys.exit(1)


def install_from_url(url):
    o = urlparse.urlsplit(url)
    path = o.path
    file_name = path.split('/')[-1]
    downloadfile = project_root_path + '/temp/' + file_name
    urllib.urlretrieve(url, downloadfile)
    install_from_zipfile(downloadfile, remove=True)


def install_from_zipfile(zipfilepath, remove=False):
    if not os.path.isfile(zipfilepath):
        print 'The given zipfile is not valid.'
        sys.exit(1)
    with zipfile.ZipFile(zipfilepath, "r") as z:
        z.extractall(project_root_path + '/package-data/www')
    if remove:
        os.remove(zipfilepath)
    os.chdir(project_root_path + '/docker')
    statement = ('docker-compose ' +
                 'run ' +
                 '--user ' +
                 'www-data ' +
                 'apache ' +
                 'php ' +
                 'recovery/install/index.php ' +
                 '--db-host=mysql ' +
                 '--db-user=shopware ' +
                 '--db-password=shopware ' +
                 '--db-name=shopware ' +
                 '--shop-host=shopware.localhost ' +
                 '--shop-locale=de_DE ' +
                 '--shop-currency=EUR ' +
                 '--admin-locale=de_DE ' +
                 '--shop-email="shop@shopware.localhost" ' +
                 '--admin-username=demo ' +
                 '--admin-password=demo ' +
                 '--admin-email=admin@shopware.localhost ' +
                 '--admin-locale=de_DE ' +
                 '--admin-name="Demo Admin" ' +
                 '--no-skip-import ' +
                 '-n')
    exitcode = call(shlex.split(statement))
    if exitcode > 0:
        sys.exit(1)


def install_from_release(releasenumber):
    os.chdir(project_root_path + '/docker')
    vars = {'releasenumber': releasenumber}
    statement = ('docker-compose ' +
                 'run ' +
                 '--user ' +
                 'www-data ' +
                 'apache ' +
                 'sw ' +
                 'install:release ' +
                 '--release={releasenumber} ' +
                 '--install-dir=/var/www/shopware ' +
                 '--db-host=mysql ' +
                 '--db-user=shopware ' +
                 '--db-password=shopware ' +
                 '--db-name=shopware ' +
                 '--shop-host=shopware.localhost ' +
                 '--shop-locale=de_DE ' +
                 '--shop-currency=EUR ' +
                 '--admin-locale=de_DE ' +
                 '--shop-email="shop@shopware.localhost" ' +
                 '--admin-username=demo ' +
                 '--admin-password=demo ' +
                 '--admin-email=admin@shopware.localhost ' +
                 '--admin-locale=de_DE ' +
                 '--admin-name="Demo Admin" ' +
                 '-n').format(**vars)
    exitcode = call(shlex.split(statement))
    if exitcode > 0:
        sys.exit(1)


def docker_stop():
    os.chdir(project_root_path + '/docker')
    exitcode = call(shlex.split("docker-compose stop"))
    if exitcode > 0:
        sys.exit(1)


def docker_down():
    os.chdir(project_root_path + '/docker')
    exitcode = call(shlex.split("docker-compose down --remove-orphans"))
    if exitcode > 0:
        sys.exit(1)


def docker_up():
    os.chdir(project_root_path + '/docker')
    exitcode = call(shlex.split("docker-compose up -d --force-recreate"))
    if exitcode > 0:
        sys.exit(1)


def remove_package_data():
    delete_directory_contents(project_root_path + '/package-data/www')
    delete_directory_contents(project_root_path + '/package-data/mysql')


def delete_directory_contents(path):
    exclude_in_root = set(['.gitignore', '.gitkeep'])
    for root, dirs, files in os.walk(path, topdown=False):
        for name in files:
            current_dir = os.path.dirname(os.path.join(root, name))
            if current_dir != path or name not in exclude_in_root:
                os.remove(os.path.join(root, name))
        for name in dirs:
            os.rmdir(os.path.join(root, name))


def main(argv):
    usage_help = 'Usage: run.py (-r <release number>|-z <zipfile>|-u <url>)'
    releasenumber = ''
    zipfile = ''
    zipurl = ''
    try:
        opts, args = getopt.getopt(argv, 'hr:z:u:', ['release=', 'zipfile=', 'url='])
    except getopt.GetoptError:
        print usage_help
        sys.exit(1)
    for opt, arg in opts:
        if opt == '-h':
            print usage_help
            sys.exit()
        elif opt in ('-r', '--release'):
            releasenumber = arg
        elif opt in ('-z', '--zipfile'):
            zipfile = arg
        elif opt in ('-u', '--url'):
            zipurl = arg

    if not releasenumber and not zipfile and not zipurl:
        print usage_help
        sys.exit(1)

    docker_down()
    remove_package_data()
    # docker_up()

    if releasenumber:
        install_from_release(releasenumber)
    elif zipfile:
        install_from_zipfile(zipfile)
    elif zipurl:
        install_from_url(zipurl)

    grant_api_access('demo')
    disable_first_run_wizard()

    # docker_stop()  # Neccessary to recreate links between containers

    run_testsuite()

    docker_down()


if __name__ == '__main__':
    main(sys.argv[1:])
