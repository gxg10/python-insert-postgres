import psycopg2
from config import config

def connect():
    conn = None
    try:
        params = config()
        print('connecting tot he postgresql database boss..')
        conn = psycopg2.connect(**params)

        cur = conn.cursor()

        print('postgresql database version:')
        cur.execute('SELECT version()')

        db_version = cur.fetchone()
        print(db_version)

        cur.close()
    except (Exception, psycopg2.DatabaseError) as error:
        print(error)
    finally:
        if conn is not None:
            conn.close()
            print('database conn closed')


if __name__=='__main__':
    connect()
