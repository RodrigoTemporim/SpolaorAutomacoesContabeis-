<style>
    body {
        background-color: #eee;
    }

    img {}

    .h1-center {

        text-align: center;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background-color:  #024A7F;">
    <div class="container-fluid navbar-container">
        <a class="navbar-brand" href="<?= base_url('base/index') ?>">
            <img class="img-fluid" src="<?= base_url('img/logo-lado.svg') ?>" alt="Logolado">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Itens de navegação  teste-->
            </ul>
            <!-- Formulário de pesquisa -->
        </div>
    </div>
</nav>
<button class='btn btn-secondary mt-3 m-lg-3' onclick="window.history.back();">Voltar</button></button>
<div class="container mb-3 h1-center">    
    <form action="http://localhost:8080/Conciliacao/csv" method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
            <label for="arquivo" class="form-label fs-5 ">Selecione um arquivo CSV para fazer o upload:</label>
            <input type="file" class="form-control border-primary" id="arquivo" name="arquivo">
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>
<div class="view">


    <?php if ($csvData): ?>

        <div class="container d-flex justify-content-center align-items-center min-vh-100 mt-3 mb-3">

            <div class="card" style="border-radius: 2vh; width: 100%;">

                <div class="card-body m-lg-3 ">
                    <h1 class="h1-center mb-1">Dados do CSV</h1>
                    <!-- <div class="container d-flex flex-column align-items-end">
                        <div class="mb-3">
                            <label for="Conciliado" class="form-label">Conciliado:</label>
                            <input type="checkbox" id="filtro4" name="filtro4" onchange="aplicarFiltros()"> 
                        </div>

                        <div class="mb-3">
                            <label for="Credito" class="form-label">Crédito:</label>
                            <input type="checkbox" id="filtro5" name="filtro5" onchange="aplicarFiltros()"> 
                        </div>

                        <div class="mb-3">
                            <label for="Debito" class="form-label">Débito:</label>
                            <input type="checkbox" id="filtro5" name="filtro5" onchange="aplicarFiltros()"> 
                        </div>
                    </div> -->
                </div>


                <div class="table-responsive text-center">                  

                    <table class="table">

                        <thead>
                            <tr>
                                <?php foreach ($csvData[0] as $header): ?>
                                    <th>
                                        <?= $header ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i < count($csvData); $i++): ?>
                                <tr>
                                    <?php foreach ($csvData[$i] as $value): ?>
                                        <td>
                                            <?= $value ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>

                </div>

            </div>

        </div>

    </div>

<?php else: ?>
    <br>
    <h3 class='h1-center'>Nenhum dado para exibir.</h3>
<?php endif; ?>



</div>