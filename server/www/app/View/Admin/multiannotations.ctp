Submission ID,Marker,Annotation Type,Page,X (%),Y (%),Width (%),Height (%),Filename,Title,Duration (Seconds)
<?php
	foreach($submissions as $submission) {
		foreach($submission['annotations'] as $annotation) {
			echo $submission['Submission']['id'].',';
			echo $annotation->marker.',';
			echo $annotation->type.',';
			echo $annotation->page_no.',';
			echo $annotation->x_percentage.',';
			echo $annotation->y_percentage.',';
			echo $annotation->width_percentage.',';
			echo $annotation->height_percentage.',';
			if(isset($annotation->filename)) {
				echo str_replace("\n",".  ",str_replace(",",".",$annotation->filename)).',';
			} else {
				echo ',';
			}
			echo str_replace("\n",".  ",str_replace(",",".",$annotation->title)).',';
			if(isset($annotation->duration)) {
				echo str_replace("\n",".  ",str_replace(",",".",$annotation->duration)).',';
			} else {
				echo ',';
			}
			echo "\n";
		}
	}
?>