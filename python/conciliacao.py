import pandas as pd
import numpy as np 
import sys 

data_frame = pd.DataFrame()

def newColumnString(file, string, string1):
    file[string].str.replace(r'^(0+)', '', regex=True).fillna(0)
    file[string1] = file[string].str.split('-', n=1).str.get(0)
    file[string1] = file[string1].str.replace('\D+', '', regex=True)

def notString(file, string):
    file[string] = file[string].str.replace('\D+', '', regex=True)

def intoNumber(file, string, string1):
    file = file.dropna(how='all', subset=['Saldo'])
    file = file.fillna({string: 0, string1: 0}).fillna(0)
    file[[string, string1]] = (file[[string, string1]].apply(pd.to_numeric)).astype(float)
    return file

def processarArquivo(file_path, saida):
        
        csv_df = pd.read_csv(file_path, encoding='latin-1', sep=';', decimal=',',skip_blank_lines=True, skiprows=[427])   
        remocao1 = 'DEBITADO REF. SALARIO'
        remocao2 = 'PAGAMENTO REF. SALARIO -'

        #PRECISO FAZER ISSO DE UMA FORMA QUE FIQUEM TUDO JUNTO OK ? SEM TER ESSES NEGOCIOS DE ROMACAO
        csv_df['DATA'] = csv_df['Histórico'].str.extract(r'(\d{2}/\d{2}/\d{4})')
        csv_df['Histórico'] = csv_df['Histórico'].replace(to_replace=r'\d{2}/\d{2}/\d{4}', value='', regex=True).str.strip()
        csv_df['Histórico'] = csv_df['Histórico'].str.replace(remocao1, '')      
        csv_df['Histórico'] = csv_df['Histórico'].str.replace(remocao2, '')
        csv_df.reindex(columns=['Histórico', 'DATA', ])
    
        
 
        # else:
        #     newColumnString(csv_df, 'Histórico', 'Nº da Nota')                   

        csv_df.dropna(subset=['Histórico'], inplace=True)
        notString(csv_df, 'Débito')
        notString(csv_df, 'Crédito')
        # csv_df = intoNumber(csv_df, 'Crédito', 'Débito')
        # csv_df.eval('Conciliação = Débito - Crédito', inplace=True, target=csv_df)
        # csv_dfClean = csv_df.drop(['Crédito', 'Débito'], axis=1)
        # # novo_csvdf = csv_dfClean.groupby(['Nº da Nota'])['Conciliação'].sum().reset_index()
        # novo_csvdf['Conciliação'] = (novo_csvdf['Conciliação'] * 0.01).round(2)
        # newIndex = novo_csvdf.sort_values(by=['Conciliação'])
        # newIndexNoDuplicates = newIndex.drop_duplicates()
        newIndexNoDuplicates = csv_df
        # Salve o resultado em um arquivo na pasta 'tempArq'
        newIndexNoDuplicates.to_excel('texte.xlsx', index=False)
        
if __name__ == "__main__":
    # Substitua os caminhos pelos seus
    caminho_arquivo = "H:/spoloar/xxxx.csv"
    caminho_saida = "H:/spoloar/"

    processarArquivo(caminho_arquivo, caminho_saida)