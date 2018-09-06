import psycopg2
from config import config
from fpdf import FPDF

def select_vendor():

    sql = """ SELECT * FROM vendor"""
    conn = None
    try:
        params = config()
        conn = psycopg2.connect(**params)
        cur = conn.cursor()
        cur.execute(sql)
        test = cur.fetchone()
    except (Exception, psycopg2.DatabaseError) as error:
        print (error)
    finally:
        if conn is not None:
            conn.close()
    return test

class PDF(FPDF):
    def header(self):

        self.set_font('Arial', '', 14)
    def footer(self):
        self.set_y(-15)
        self.set_font('Arial', '', 8)

pdf = PDF()
pdf.alias_nb_pages()
pdf.add_page()
test1 = select_vendor()[0]
pdf.write(5, test1)
pdf.output('test3.pdf', 'F')

##print (select_vendor())
