<style> 
body {
  background-color: #eee;
}
img{
    
}

.h1-center{

    text-align: center;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background-color:  #024A7F;">
    <div class="container-fluid navbar-container">
      <a class="navbar-brand" href="<?= base_url('base/index') ?>">
          <img class="img-fluid" src="<?= base_url('img/logo-lado.svg') ?>" alt="Logolado">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
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

  <div class="container mb-3">
  <form action="http://localhost:8080/Conciliacao/pandas" method="POST" enctype="multipart/form-data">
        Selecione um arquivo CSV para fazer o upload:
        <input type="file" name="arquivo">
        <input type="submit" value="Enviar">
    </form>
</div>
<div class="view">

<h1 class="h1-center mb-3">Dados do CSV</h1>

    <?php if ($csvData): ?>

        <div class="container d-flex justify-content-center align-items-center min-vh-100 mt-n5 mb-3">

            <div class="card" style="border-radius: 2vh; width: 100%;">

                <div class="card-body m-lg-5 text-center">

                    <div class="table-responsive">

                        <table class="table">

                            <thead>
                                <tr>
                                    <?php foreach ($csvData[0] as $header): ?>
                                        <th><?= $header ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i < count($csvData); $i++): ?>
                                    <tr>
                                        <?php foreach ($csvData[$i] as $value): ?>
                                            <td><?= $value ?></td>
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

        <p>Nenhum dado para exibir.</p>

    <?php endif; ?>

</div>
