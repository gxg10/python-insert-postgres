from fpdf import FPDF

class PDF(FPDF):

    def header(self):

        self.set_font('Arial', '', 14)
    def footer(self):
        self.set_y(-15)
        self.set_font('Arial', '', 8)

pdf = PDF()
pdf.alias_nb_pages()
pdf.add_page()
pdf.write(5, 'blabla ba bla')
pdf.output('test2.pdf', 'F')
