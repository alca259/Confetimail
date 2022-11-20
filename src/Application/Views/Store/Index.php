<div class="container" id="StorePage">

    <div class="store-container">
        <div class="row">
            <div class="col-md-6">
                <div>
                    <img src="/Public/img/store/tazas1.jpg" class="featurette-image img-responsive center-block" />
                </div>
                <span class="price">9 &euro;</span>
                <span class="description">
                    <?php echo T_("Product.Text.1"); ?>
                </span>
            </div>
            <div class="col-md-6">
                <div>
                    <img src="/Public/img/store/tazas2.jpg" class="featurette-image img-responsive center-block" />
                </div>
                <span class="price">9 &euro;</span>
                <span class="description">
                    <?php echo T_("Product.Text.2"); ?>
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php echo T_("Store.Text.Selling"); ?>
            </div>
        </div>
    </div>

    <hr class="featurette-divider">
    <?php
    require_once("Application/Views/Shared/_LayoutFooter.php");
    ?>
</div>