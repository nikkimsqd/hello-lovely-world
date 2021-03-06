<?php $__env->startSection('content'); ?>

<section class="content">
  <div class="row">
    <div class="row">
      <div class="col-md-12">
        <div class="col-md-9">
        
          <div class="total-products">
              <p><span><?php echo e($productCount); ?></span> products found</p>
          </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
             <a class="btn btn-block btn-info" href="<?php echo e(url('addproduct')); ?>">Add Products here</a>
            </div>
        </div>
      </div>
    </div>

  <?php if(empty($products)): ?>
    <label>You have no products in your store</label>
  <?php else: ?>
  <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <div class=" col-12 col-sm-6 col-lg-4" style="padding-right: 20px; padding-left: 20px;"> <!-- change to col-lg-3 if dako ra -->
      <div class="box " style="padding: 10px;">
        <div class="box-body">
          <?php $counter = 1; ?>

          <?php $__currentLoopData = $product->productFile; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

          <?php if($counter == 1): ?>  
            <img src="<?php echo e(asset('/uploads').$image['filepath']); ?>" style="width:100%; height: 350px; object-fit: cover;">
          <?php else: ?>
          <?php endif; ?>
          <?php $counter++; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

          <div class="row">
            <a href="<?php echo e(url('viewproduct/'.$product['id'])); ?>">
              <h4><?php echo e($product['productName']); ?></h4>
            </a>
            <h2></h2>

            <a href="<?php echo e(url('viewproduct/'.$product['id'])); ?>" class="btn btn-block btn-primary">View Product</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
  </div>
</section>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('scripts'); ?>
<script type="text/javascript">

$('.products').addClass("active");
$('.allproducts').addClass("active");

</script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('boutique.sections', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('layouts.boutique', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>