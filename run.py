from subprocess import Popen, PIPE, STDOUT
import sys


def run_cmd(command):
    print('*' * 50)
    print(' '.join(command))
    print('*' * 50)
    process = Popen(command, stdout=sys.stdout, stderr=sys.stderr, shell=True)
    try:
        stdout, stderr = process.communicate()
    except KeyboardInterrupt:
        process.kill()


def print_help():
    print("App management guide.\nUse this commands to manage app:\n"\
            " start      - for running parsers and queue\n"\
            " stop       - for stopping containers\n"\
            " remove-all - for removing all docker data\n"\
            " migrate    - for applying migrations")


def main(c):
    commands = ('start', 'stop', 'remove-all', 'migrate')
    if c in commands:
        if c == commands[0]:
            run_cmd(['docker-compose', 'up', '--build'])
        elif c == commands[1]:
            run_cmd(['docker-compose', 'down'])
        elif c == commands[2]:
            run_cmd(['docker-compose', 'down', '--rmi', 
            'all', '-v', '--remove-orphans'])
        elif c == commands[3]:
            run_cmd(['docker-compose', 'run', 'python', 'alembic',
            'upgrade', 'head'])
        return
    
    print_help()
    return


if __name__=="__main__":
    args = sys.argv

    if len(args) != 2:
        print_help()
    else:
        main(args[1])
