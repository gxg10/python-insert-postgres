import psycopg2
from config import config
import csv
from collections import defaultdict

columns = defaultdict(list)

with open('ordv2.txt') as f:

    reader = csv.reader(f, delimiter="\t")
    next(reader, None)
    included = [1, 3, 4, 5, 6, 7, 8, 9, 27, 34, 36, 43]
    tup_list = []
    for row in reader:
        content = list(row[i] for i in included)
        tup_list.append(content)
##    print (tup_list[0])


def insert_vendor(vendor_list):
    sql = """INSERT INTO ord6 VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
    conn = None
    try:
        params = config()
        conn = psycopg2.connect(**params)
        cur = conn.cursor()
##        data = tuple((vendor_name,title, name, nr))
        cur.executemany(sql, vendor_list)
        conn.commit()
        cur.close()
    except (Exception, psycopg2.DatabaseError) as error:
        print(error)
    finally:
        if conn is not None:
            conn.close()

def select_from():
    sql = """ SELECT * FROM ord6 where simbol = 'AMO' """
    conn = None
    try:
        params = config()
        conn = psycopg2.connect(**params)
        cur = conn.cursor()
        cur.execute(sql)
        test = cur.fetchall()
    except (Exception, psycopg2.DatabaseError) as error:
        print(error)
    finally:
        if conn is not None:
            conn.close()
    return test

print (select_from())

##
##if __name__ == '__main__':
##    # insert one vendor
####    insert_vendor(tup_list)
##    select_from()


## cod vechi
    
##with open('orders1.txt', 'r') as f:
##    reader = csv.reader(f, delimiter="\t")
##    next(reader, None)
##    reader_list = list(reader)
##    tup_list = []
##    for i in reader_list:
##        tup_list.append(tuple(i))
##    
##    print (tup_list)
