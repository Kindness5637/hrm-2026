<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/vendor/orgchart/css/jquery.orgchart.css">

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="mb-0"><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('xin_org_chart_title');?></h6>
    <div>
      <button class="btn btn-sm btn-outline-secondary" id="orgchart-zoomin" title="Zoom In"><i class="fa fa-search-plus"></i></button>
      <button class="btn btn-sm btn-outline-secondary" id="orgchart-zoomout" title="Zoom Out"><i class="fa fa-search-minus"></i></button>
      <button class="btn btn-sm btn-outline-secondary" id="orgchart-reset" title="Reset"><i class="fa fa-refresh"></i></button>
    </div>
  </div>
  <div class="card-block" style="overflow:auto;min-height:500px;">
    <div id="chart-container"></div>
  </div>
</div>

<script src="<?php echo base_url();?>skin/hrsale_assets/vendor/orgchart/js/jquery.orgchart.js"></script>
<script>
(function($) {
  var orgData = <?php echo json_encode($orgchart_data); ?>;

  function buildNode(data) {
    var node = {
      name: data.name,
      title: data.title || '',
      id: data.id || ''
    };
    if (data.photo) {
      node.photo = data.photo;
    }
    if (data.children && data.children.length > 0) {
      node.children = [];
      for (var i = 0; i < data.children.length; i++) {
        node.children.push(buildNode(data.children[i]));
      }
    }
    return node;
  }

  var chartData = buildNode(orgData);

  var $chart = $('#chart-container');

  function renderChart(data) {
    $chart.orgchart({
      'data': data,
      'nodeTitle': 'name',
      'nodeId': 'id',
      'direction': 't2b',
      'pan': true,
      'zoom': true,
      'zoominLimit': 7,
      'zoomoutLimit': 0.5,
      'exportButton': false,
      'chartClass': 'orgchart-custom',
      'createNode': function($node, data) {
        // Add photo and title styling
        var photo = data.photo || '';
        var title = data.title || '';
        if (photo) {
          $node.find('.title').before('<div style="text-align:center;margin-bottom:5px;"><img src="' + photo + '" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid #3e70c9;" onerror="this.src=\'<?php echo base_url();?>uploads/profile/default.jpg\'"></div>');
        }
        if (title) {
          $node.find('.title').after('<div class="content" style="font-size:11px;color:#888;">' + title + '</div>');
        }
      }
    });
  }

  renderChart(chartData);

  // Zoom controls
  var currentScale = 1;
  $('#orgchart-zoomin').click(function() {
    currentScale += 0.1;
    $chart.find('.orgchart').css('transform', 'scale(' + currentScale + ')').css('transform-origin', 'top center');
  });
  $('#orgchart-zoomout').click(function() {
    if (currentScale > 0.3) {
      currentScale -= 0.1;
      $chart.find('.orgchart').css('transform', 'scale(' + currentScale + ')').css('transform-origin', 'top center');
    }
  });
  $('#orgchart-reset').click(function() {
    currentScale = 1;
    $chart.find('.orgchart').css('transform', 'scale(1)').css('transform-origin', 'top center');
    $chart.find('.orgchart').css('transform', '');
  });

})(jQuery);
</script>

<style>
.orgchart-custom .node {
  background: #fff;
  border: 2px solid #3e70c9;
  border-radius: 8px;
  padding: 10px 15px;
  min-width: 140px;
  text-align: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  transition: all 0.2s;
}
.orgchart-custom .node:hover {
  box-shadow: 0 4px 12px rgba(62,112,201,0.3);
  transform: translateY(-2px);
}
.orgchart-custom .node .title {
  font-weight: bold;
  color: #333;
  font-size: 13px;
}
.orgchart-custom .node .content {
  font-size: 11px;
  color: #888;
  margin-top: 2px;
}
.orgchart-custom .lines {
  border-color: #3e70c9;
}
.orgchart-custom .node.focused {
  background-color: #e8f0fe;
  border-color: #1a56c4;
}
#chart-container {
  display: flex;
  justify-content: center;
  padding: 20px 0;
}
</style>
