import psycopg2
from config import config
import csv

with open('ord.txt', 'r') as f:
    reader = csv.reader(f, delimiter="\t")
    d = list(reader)
##    t = tuple(d[1])
    titlu = d[1][0]
    nume = d[1][1]
    locatie = d[1][2]
    numar = d[1][3]

    

def insert_vendor(vendor_name,title, name, nr):
    sql = """INSERT INTO vendor VALUES(%s, %s, %s, %s)"""
    conn = None
    try:
        params = config()
        conn = psycopg2.connect(**params)
        cur = conn.cursor()
        #data = (vendor_name, name, nr, )
        data = tuple((vendor_name,title, name, nr))
        cur.execute(sql, data)
        conn.commit()
        cur.close()
    except (Exception, psycopg2.DatabaseError) as error:
        print(error)
    finally:
        if conn is not None:
            conn.close()

if __name__ == '__main__':
    # insert one vendor
    insert_vendor(titlu, nume, locatie, numar)
