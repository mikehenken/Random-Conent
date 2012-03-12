<?php

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");
$random_content_file=GSDATAOTHERPATH .'random_content.xml';

# register plugin
register_plugin(
	$thisfile, 													# ID of plugin, should be filename minus php
	'Random Content', 									# Title of plugin
	'1.0', 															# Version of plugin
	'Mike Henken',											# Author of plugin
	'http://www.michaelhenken.com/', 						# Author URL
	'Allows For Easy Insertion Of Random Content Or Banners', 	# Plugin Description
	'pages', 											# Page type of plugin
	'random_content_process'  										# Function that displays content
);

# hooks
add_action('pages-sidebar','createSideMenu',array($thisfile,'Random content')); 

define('contentDATAFILE', GSDATAOTHERPATH  . 'random_content.xml');

global $error_cate;
$error_cate = '';

global $EDLANG, $EDOPTIONS, $toolbar, $EDTOOL;
if (defined('GSEDITORLANG')) { $EDLANG = GSEDITORLANG; } else {	$EDLANG = 'en'; }
if (defined('GSEDITORTOOL')) { $EDTOOL = GSEDITORTOOL; } else {	$EDTOOL = 'basic'; }
if (defined('GSEDITOROPTIONS') && trim(GSEDITOROPTIONS)!="") { $EDOPTIONS = ", ".GSEDITOROPTIONS; } else {	$EDOPTIONS = ''; }
if ($EDTOOL == 'advanced') {
$toolbar = "
	    ['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Table', 'TextColor', 'BGColor', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source'],
    '/',
    ['Styles','Format','Font','FontSize']
";
} elseif ($EDTOOL == 'basic') {
$toolbar = "['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source']";
} else {
$toolbar = GSEDITORTOOL;
}

function xml2array ( $xmlObject, $out = array () )
{
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;

        return $out;
}

function shuffle_assoc(&$array)
{
	$keys = array_keys($array);

	shuffle($keys);

	foreach($keys as $key) {
		$new[$key] = $array[$key];
	}

	$array = $new;

	return $array;
}

function random_content($c_category)
{
	if(file_exists(contentDATAFILE))
	{	
		$category_file = getXML(contentDATAFILE);
	}
	foreach($category_file->category as $category)
	{
		if($category['name'] == $c_category)		
		{	
			$number_content = $category['limit'];
			$category_count = count($category->content);
			$category_array = xml2array($category);
			
			$new_array = shuffle_assoc($category_array['content']);
			
			$number_content_after = $number_content;
			if($category_count > 1)
			{
				foreach($new_array as $content)
				{
					if($number_content_after >= 1)
					{	
						echo $content;
					}
					$number_content_after = $number_content_after-1;
				}
			}
			else
			{
				echo $category->content;
			}
		}
	}
}

function random_content_add_content() {
$new_content_title = $_POST['title'];
$new_content_contents = $_POST['contents'];
global $error_cate;
if(file_exists(contentDATAFILE))
{	
	$category_file = getXML(contentDATAFILE);
}
$xml= new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');

if($_POST['category'] == "")
{
	$error_cate = '<div class="error">Please Choose Category For Content</div>';
}
else 
{
	$error_cate = '<div class="updated">Content Succesfully Created</div>'; 

	foreach($category_file->category as $category)
	{
		$c_atts= $category->attributes();
		$c_child = $xml->addChild('category');
		$c_child->addAttribute('name', $c_atts['name']);
		$c_child->addAttribute('limit', $c_atts['limit']);
		foreach($category->content as $content)
		{
			$atts= $content->attributes();
			$child = $c_child->addChild('content');
			$child->addAttribute('title', $atts['title']);
			$child->addCData($content);
		}
		if($_POST['category'] == $c_atts['name'])
		{
			$child = $c_child->addChild('content');
			$child->addAttribute('title', $_POST['title']);
			$child->addCData($_POST['contents']);
		}
		
	}
	XMLsave($xml, contentDATAFILE);
}
return $error_cate;
}

function random_content_edit_content()
{
	$edit_content_title = $_POST['title'];
	$edit_content_contents = $_POST['contents'];
	if(file_exists(contentDATAFILE))
	{	
		$category_file = getXML(contentDATAFILE);
	}
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');

	foreach($category_file->category as $category)
	{
		$c_atts= $category->attributes();
		$c_child = $xml->addChild("category");
		$c_child->addAttribute('name', $c_atts['name']);
		$c_child->addAttribute('limit', $c_atts['limit']);

		foreach($category->content as $content)
		{
			$atts= $content->attributes();
			if($_POST['category'] == $c_atts && $_POST['old-title'] == $atts)
			{
				$child = $c_child->addChild('content');
				$child->addAttribute('title', $edit_content_title);
				$child->addCData($edit_content_contents);
			}
			else
			{
				$child = $c_child->addChild('content');
				$child->addAttribute('title', $atts['title']);
				$child->addCData($content);
			}
		}
	}
	XMLsave($xml, contentDATAFILE);
}

function random_content_add_category() {


if(file_exists(contentDATAFILE))
{	
	$category_file = getXML(contentDATAFILE);
}
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');

	foreach($category_file->category as $category)
	{
		$c_atts= $category->attributes();
		$c_child = $xml->addChild("category");
		$c_child->addAttribute('name', $c_atts['name']);
		$c_child->addAttribute('limit', $c_atts['limit']);

		foreach($category->content as $content)
		{
			$atts= $content->attributes();
			$child = $c_child->addChild('content');
			$child->addAttribute('title', $atts['title']);
			$child->addCData($content);
		}
	}
	$child = $xml->addChild("category");
	$child->addAttribute('name', $_POST['title']);
	$child->addAttribute('limit', $_POST['category_limit']);
	XMLsave($xml, contentDATAFILE);
}

function random_content_delete_content() {
if(file_exists(contentDATAFILE))
{	
	$category_file = getXML(contentDATAFILE);
}
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');

	foreach($category_file->category as $category)
	{
		$c_atts= $category->attributes();
		$c_child = $xml->addChild("category");
		$c_child->addAttribute('name', $c_atts['name']);
		$c_child->addAttribute('limit', $c_atts['limit']);

		foreach($category->content as $content)
		{
			$atts= $content->attributes();
			if($atts['title']!=$_GET['delete'] OR $c_atts['name'] != $_GET['category_of_deleted']){
				$child = $c_child->addChild('content');
				$child->addAttribute('title', $atts['title']);
				$child->addCData($content);
			}
		}
	}
	XMLsave($xml, contentDATAFILE);
}

function random_content_delete_category() 
{

if(file_exists(contentDATAFILE))
{	
	$category_file = getXML(contentDATAFILE);
}
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');

	foreach($category_file->category as $category)
	{
		$c_atts= $category->attributes();
		if($c_atts['name'] != $_GET['delete_category'])
		{
			$c_child = $xml->addChild("category");
			$c_child->addAttribute('name', $c_atts['name']);
			$c_child->addAttribute('limit', $c_atts['limit']);

			foreach($category->content as $content)
			{
				$atts= $content->attributes();
				$child = $c_child->addChild('content');
				$child->addAttribute('title', $atts['title']);
				$child->addCData($content);
			}
		}
	}
	XMLsave($xml, contentDATAFILE);
}

function random_content_edit_category() 
{
	if(file_exists(contentDATAFILE))
	{	
		$category_file = getXML(contentDATAFILE);
	}
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
	$old_title =  $_POST['old_title'];
	foreach($category_file->category as $category)
	{
		$c_atts= $category->attributes();
		if($c_atts['name'] == $old_title)
		{
			$c_child = $xml->addChild("category");
			$c_child->addAttribute('name', $_POST['title']);
			$c_child->addAttribute('limit', $_POST['category_limit']);

			foreach($category->content as $content)
			{
				$atts= $content->attributes();
				$child = $c_child->addChild('content');
				$child->addAttribute('title', $atts['title']);
				$child->addCData($content);
			}
		}
		else
		{
			$c_child = $xml->addChild("category");
			$c_child->addAttribute('name', $c_atts['name']);
			$c_child->addAttribute('limit', $c_atts['limit']);

			foreach($category->content as $content)
			{
				$atts= $content->attributes();
				$child = $c_child->addChild('content');
				$child->addAttribute('title', $atts['title']);
				$child->addCData($content);
			}
		}
	}
	XMLsave($xml, contentDATAFILE);
}

function random_content_process() {
	global $error_cate;
	if(isset($_GET['delete']))
	{
		random_content_delete_content();
		random_content_form($error_cate);
	}
	elseif(isset($_GET['add-new']))
	{
		random_content_add_content();
		random_content_form($error_cate);
	}
	elseif(isset($_GET['edit-content']))
	{
		random_content_edit_content();
		random_content_form($error_cate);
	}
	elseif(isset($_GET['add_category']))
	{
		random_content_add_category();
		random_content_form($error_cate);
	}
	elseif(isset($_GET['submit_edit_category']))
	{
		random_content_edit_category();
		random_content_form($error_cate);
	}
	elseif(isset($_GET['delete_category']))
	{
		random_content_delete_category();
		random_content_form($error_cate);
	}
	else
	{
		random_content_form($error_cate);
	}
}

	function random_content_form($error_cate)
	{
		if(isset($error_cate))
		{
			if($error_cate != "")
			$error_cate = $error_cate;
		}
		else
		{
			$error_cate = "";
		}
	?>
<div style="width:100%;margin:0 -15px -15px -10px;padding:0px;">
	<h3 class="floated">Random content</h3>  
	<div class="edit-nav clearfix" style="">
		<a href="load.php?id=RandomContent&help" <?php if (isset($_GET['help'])){ echo 'class="current"'; } ?>>Help</a>
		<a href="load.php?id=RandomContent&categories" <?php if (isset($_GET['categories'])){ echo 'class="current"'; } ?>>Manage Categories</a>
		<a href="load.php?id=RandomContent&add" <?php if (isset($_GET['add']) && $_GET['add'] == "") { echo 'class="current"'; } ?>>Add New Content</a>
		<a href="load.php?id=RandomContent&view" <?php if (isset($_GET['view'])) { echo 'class="current"'; } ?>>View All Content</a>
	</div> 
</div>
</div>
<div class="main" style="margin-top:-10px;">

<?php
      if(!file_exists(contentDATAFILE))
		{
		$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
		$xml->asXML(contentDATAFILE);
		 return true;
		}
	if(isset($_GET['add']))
	{
		global $SITEURL, $toolbar, $EDOPTIONS, $EDLANG;
?>
		<h3>Add New Content</h3>
		<?php 
		if(isset($error_cate))
		{
			echo $error_cate;
		} 
		?>
		<form action="load.php?id=RandomContent&add&add-new" method="post" accept-charset="utf-8">
		<input type="text" name="title" class="text" style="width:635px;" value="Title.." onFocus="if(this.value == 'Title..') {this.value = '';}" onBlur="if (this.value == '') {this.value = 'Title..';}" />
		
		<select name="category" class="text" style="width:647px;margin:5px 0px 5px 0px">
			<option value="">Choose Category</option>
			<?php
				$content_file = getXML(contentDATAFILE);
				foreach($content_file->category as $edit_cate)
				{
				$atts= $edit_cate->attributes();
					echo '<option value="'.$atts['name'].'">'.$atts['name'].'</option>';
				}
			?>
		</select>

		<textarea name="contents"></textarea>
		<script type="text/javascript" src="template/js/ckeditor/ckeditor.js"></script>
		<script type="text/javascript">
		  // missing border around text area, too much padding on left side, ...
		  $(function() {
		    CKEDITOR.replace( 'contents', {
			        skin : 'getsimple',
			        forcePasteAsPlainText : false,
			        language : '<?php echo $EDLANG; ?>',
			        defaultLanguage : '<?php echo $EDLANG; ?>',
			        entities : true,
			        uiColor : '#FFFFFF',
					    height: '200px',
					    baseHref : '<?php echo $SITEURL; ?>',
			        toolbar : [ <?php echo $toolbar; ?> ]
					    <?php echo $EDOPTIONS; ?>
		    })
		  });
		</script>
		<input type="submit" class="submit" value="Add Content" style="float:right;"/>
		</form>
		<div style="clear:both">&nbsp;</div>
<?php
	}

	elseif(isset($_GET['edit']))
	{
		global $SITEURL, $toolbar, $EDOPTIONS, $EDLANG;
		$content_file = getXML(contentDATAFILE);
		foreach($content_file->category as $edit_cate)
		{	
		$c_atts= $edit_cate->attributes();
		
		foreach($edit_cate->content as $edit_content)
			{
			$atts= $edit_content->attributes();
				if(urldecode($_GET['edit']) == $atts['title'])
				{
?>
				<h3>Edit Content</h3>
				<form action="load.php?id=RandomContent&edit-content" method="post" accept-charset="utf-8">
				<input type="text" name="title" class="text" style="width:635px;margin-bottom:5px;" value="<?php echo $atts['title']; ?>" />
				<input type="hidden" name="old-title" value="<?php echo $atts['title']; ?>" />
				<input type="hidden" name="category" value="<?php echo $c_atts['name']; ?>" ?>
				<textarea name="contents"><?php echo $edit_content; ?></textarea>
				<script type="text/javascript" src="template/js/ckeditor/ckeditor.js"></script>
				<script type="text/javascript">
				  // missing border around text area, too much padding on left side, ...
				  $(function() {
				    CKEDITOR.replace( 'contents', {
					        skin : 'getsimple',
					        forcePasteAsPlainText : false,
					        language : '<?php echo $EDLANG; ?>',
					        defaultLanguage : '<?php echo $EDLANG; ?>',
					        entities : true,
					        uiColor : '#FFFFFF',
							    height: '200px',
							    baseHref : '<?php echo $SITEURL; ?>',
					        toolbar : [ <?php echo $toolbar; ?> ]
							    <?php echo $EDOPTIONS; ?>
				    })
				  });
				</script>
				<input type="submit" class="submit" vaule="Add Content" style="float:right;"/>
				</form>
				<div style="clear:both">&nbsp;</div>
<?php
}
			}
		}
	}
	elseif(isset($_GET['edit_category']))
	{	
		$content_file = getXML(contentDATAFILE);
		foreach($content_file->category as $edit_cate)
		{	
			$c_atts= $edit_cate->attributes();
			if(urldecode($_GET['edit_category']) == $c_atts['name'])
			{
			?>
				<h3>Edit <?php echo $c_atts['name']; ?></h3>
				<form action="load.php?id=RandomContent&categories&submit_edit_category" method="post" accept-charset="utf-8">
				<input type="hidden" name="old_title" class="text" style="width:600px;" value="<?php echo $c_atts['name']; ?>" />
				<p><input type="text" name="title" class="text" style="width:600px;" value="<?php echo $c_atts['name']; ?>" /></p>
				<p>
				<label>Choose amount of content to display at once for this category: </label>
					<select name="category_limit">
					<option value="<?php echo $c_atts['limit']; ?>"><?php echo $c_atts['limit']; ?></option>
					<?php
					$minimum = 0;
					$maximum = 20;
					while ($minimum < $maximum)
					{
						$minimum++;
						echo '<option value="'.$minimum.'">'.$minimum.'</option>';
					}
					
					?>
					</select>
				</p>
				<input type="submit" class="submit" value="Edit Category" style="float:right;"/>
				<br/><br/>
			</form>
			<?php
			}
		}
	}
	elseif(isset($_GET['categories']))
	{

?>			<h3 class="floated">Manage Categories</h3>
			<div class="edit-nav clearfix" style="">
				<a href="#" class="ra_help_button">Add New Category</a>
			</div>
			<div class="ra_help" style="display:none;padding:10px;background-color:#f6f6f6;margin:10px;">
			<h3>Add Category</h3>  
			<form action="load.php?id=RandomContent&categories&add_category" method="post" accept-charset="utf-8">
				<p><input type="text" name="title" class="text" style="width:600px;" value="Category Title.." onFocus="if(this.value == 'Category Title..') {this.value = '';}" onBlur="if (this.value == '') {this.value = 'Category Title..';}" /></p>
				<p>
				<label>Choose amount of content to display at once for this category: </label>
					<select name="category_limit">
					<?php
					$minimum = 0;
					$maximum = 20;
					while ($minimum < $maximum)
					{
						$minimum++;
						echo '<option value="'.$minimum.'">'.$minimum.'</option>';
					}
					
					?>
					</select>
				</p>
				<input type="submit" class="submit" value="Add Category" style="float:right;"/>
			</form>		<div style="clear:both">&nbsp;</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$('.ra_help_button').click(function() {
					$('.ra_help').show();
					$('.ra_help_button').hide();
				})
			})
		</script>
		</div>
<?php

			if(file_exists(contentDATAFILE))
		{	
			$category_file = getXML(contentDATAFILE);
		}

		$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
		echo '<table class="highlight">';
			$content_count = '0';
		foreach($category_file->category as $category)
		{

				$content_count++;
				$c_atts= $category->attributes();
				?>
				<tr>
				<td>
					<a href="load.php?id=RandomContent&categories&edit_category=<?php echo $c_atts['name']; ?>"><?php echo $c_atts['name']; ?></a>
				</td>
				<td class="delete">
					<a href="load.php?id=RandomContent&categories&delete_category=<?php echo $c_atts['name']; ?>" class="delconfirm" title="Delete Category: <?php echo $c_atts['name']; ?>?? This Will Delete ALL* content In The Category As Well!">
					X
					</a>
				</td>
				</tr>
<?php
		}

			echo '</table>';
			echo '<p><b>' . $content_count . '</b> content</p>';	
}
	elseif(isset($_GET['help']))
	{
?>		<h3>Theme Functions:</h3>
		<p>You can include random content in your template using the following function:</p>
		<ul>
		  <li><?php highlight_string('<?php random_content(\'categoryname\'); ?>'); ?></li>
		</ul>
		<p>Filling in the category name will return a random content from that category.<br/><strong> Make sure you place the category name exactly as it is set (case and space sensitive)</strong></p>
<?php
	}
	else{
			
		if(file_exists(contentDATAFILE))
		{	
			$category_file = getXML(contentDATAFILE);
		}

		$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');

		foreach($category_file->category as $category)
		{
			$content_count = '0';
			$c_atts=$category->attributes();
			echo '<h3>'.$c_atts['name'].'</h3><table class="highlight">';
			foreach($category->content as $the_content)
			{	
				$content_count++;
				$atts=$the_content->attributes();
				?>
				<tr>
				<td>
				<a href="load.php?id=RandomContent&edit=<?php echo $atts['title']; ?>" title="Edit Content: <?php echo $atts['title']; ?>">
				<?php echo $atts['title']; ?>
				</a>
				</td>
				<td class="delete">
				<a href="load.php?id=RandomContent&delete=<?php echo $atts['title']; ?>&category_of_deleted=<?php echo $c_atts['name']; ?>" class="delconfirm" title="Delete Content: <?php echo $atts['title']; ?>?">
				X
				</a>
				</td>
				</tr>
<?php
			}
			echo '</table>';
			echo '<p><b>' . $content_count . '</b> content</p>';
		}

}}
?>