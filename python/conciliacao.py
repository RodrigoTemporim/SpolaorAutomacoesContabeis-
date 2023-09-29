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

def processarArquivo(file_path, output_file_path):
    try:
        csv_df = pd.read_csv(file_path, encoding='latin-1', sep=';', decimal=',')
        newColumnString(csv_df, 'Histórico', 'Nº da Nota')
        notString(csv_df, 'Débito')
        notString(csv_df, 'Crédito')
        csv_df = intoNumber(csv_df, 'Crédito', 'Débito')
        csv_df.eval('Conciliação = Débito - Crédito', inplace=True, target=csv_df)
        csv_dfClean = csv_df.drop(['Crédito', 'Débito'], axis=1)
        novo_csvdf = csv_dfClean.groupby(['Nº da Nota'])['Conciliação'].sum().reset_index()
        novo_csvdf['Conciliação'] = (novo_csvdf['Conciliação'] * 0.01).round(2)
        newIndex = novo_csvdf.sort_values(by=['Conciliação'])
        newIndexNoDuplicates = newIndex.drop_duplicates()

        # Salve o resultado em um arquivo na pasta 'tempArq'
        resultado_csv = output_file_path
        newIndexNoDuplicates.to_csv(resultado_csv, index=False, encoding='utf-16', sep=';')
    except Exception as e:
        return str(e)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Uso: python conciliacao.py <caminho_do_arquivo_csv> <caminho_do_arquivo_saida>")
        sys.exit(1)

    caminho_arquivo = sys.argv[1]
    caminho_saida = sys.argv[2]
    processarArquivo(caminho_arquivo, caminho_saida)
    
def Excel_cals(file_path):
    try:
        csv_df = pd.read_csv(file_path, encoding='latin-1', sep=';', decimal=',')
        newColumnString(csv_df, 'Histórico', 'Nº da Nota')
        notString(csv_df, 'Débito')
        notString(csv_df, 'Crédito')
        csv_df = intoNumber(csv_df, 'Crédito', 'Débito')
        csv_df.eval('Conciliação = Débito - Crédito', inplace=True, target=csv_df)
        csv_dfClean = csv_df.drop(['Crédito', 'Débito'], axis=1)
        novo_csvdf = csv_dfClean.groupby(['Nº da Nota'])['Conciliação'].sum().reset_index()
        novo_csvdf['Conciliação'] = (novo_csvdf['Conciliação'] * 0.01).round(2)
        newIndex = novo_csvdf.sort_values(by=['Conciliação'])
        newIndexNoDuplicates = newIndex.drop_duplicates()
        return newIndexNoDuplicates
    except Exception as e:
        return str(e)

def filter_neg():
    df_negativo = (Excel_cals('seu_arquivo.csv').query(' Conciliação < 0'))
    print(df_negativo)

def filter_posi():
    df_positivo = (Excel_cals('seu_arquivo.csv').query(' Conciliação > 0'))
    print(df_positivo)

def filter_conc():
    df_conciliado = (Excel_cals('seu_arquivo.csv').query(' Conciliação == 0'))
    print(df_conciliado)

def compararCals(ex1):
    frame = [data_frame, ex1]
    comparado = pd.concat(frame)
    comparado[['Nº da Nota', 'Conciliação']] = (comparado[['Nº da Nota', 'Conciliação']].apply(pd.to_numeric)).astype(float)
    f_comparado = comparado.groupby('Nº da Nota')['Conciliação'].sum().reset_index()
    t_comparado = f_comparado.sort_values(by=['Conciliação'])
    t_comparado['Nº da Nota'] = np.int64(t_comparado['Nº da Nota']).astype(str)
    final_comparado = t_comparado.drop_duplicates()

if __name__ == "__main__":
    filter_neg()
    filter_posi()
    filter_conc()
