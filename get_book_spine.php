<?php

// Extract individual book spines from the input image and save them as individual images
// The input image is a photo of a bookshelf with multiple books
// The output images are individual book spines
//function extract_spines($input_image)
//{
//    .
//}

// $image is the image 'small_covers/9780449911778.jpg'
// $n_colors is the number of colors to extract; default is 1

// initialize $image to be the image 'small_covers/9780449911778.jpg'
$image = 'small_covers/9780449911778.jpg';

get_dominant_color($image);

// get the dominant color of the book spine jpg using the k-means clustering algorithm
// The input is the image of the book spine
// The output is the dominant color of the book spine
// Parameters:
//  $image - the image of the book spine
//  $n_colors - the number of colors to extract; default is 1
function get_dominant_color($image, $n_colors = 1)
{
    // Load the image
    $img = imagecreatefromjpeg($image);

    // Resize the image to 3x3 pixel
    $resized_img = imagescale($img, 3, 3);

    // Get the RGB values of the 9 pixels
    $colors = array();
    for ($x = 0; $x < 3; $x++) {
        for ($y = 0; $y < 3; $y++) {
            $rgb = imagecolorat($resized_img, $x, $y);
            $colors[] = imagecolorsforindex($resized_img, $rgb);
        }
    }

    // Save the resized image
    imagejpeg($resized_img, 'resized.jpg');

    // Run the k-means clustering algorithm to get the dominant color
    $result = kMeans($colors, $n_colors);


    $data = array(
        array(0.05, 0.95),
        array(0.1, 0.9),
        array(0.2, 0.8),
        array(0.25, 0.75),
        array(0.45, 0.55),
        array(0.5, 0.5),
        array(0.55, 0.45),
        array(0.85, 0.15),
        array(0.9, 0.1),
        array(0.95, 0.05)
    );

// Lets normalise the input data
    foreach ($data as $key => $d) {
        $data[$key] = normaliseValue($d, sqrt($d[0] * $d[0] + $d[1] * $d[1]));
    }

    echo json_encode(kMeans($data, 2));

    function initialiseCentroids(array $data, $k)
    {
        $dimensions = count($data[0]);
        $centroids = array();
        $dimmax = array();
        $dimmin = array();
        foreach ($data as $document) {
            foreach ($document as $dim => $val) {
                if (!isset($dimmax[$dim]) || $val > $dimmax[$dim]) {
                    $dimmax[$dim] = $val;
                }
                if (!isset($dimmin[$dim]) || $val < $dimmin[$dim]) {
                    $dimmin[$dim] = $val;
                }
            }
        }
        for ($i = 0; $i < $k; $i++) {
            $centroids[$i] = initialiseCentroid($dimensions, $dimmax, $dimmin);
        }
        return $centroids;
    }

    function initialiseCentroid($dimensions, $dimmax, $dimmin)
    {
        $total = 0;
        $centroid = array();
        for ($j = 0; $j < $dimensions; $j++) {
            $centroid[$j] = (rand($dimmin[$j] * 1000, $dimmax[$j] * 1000));
            $total += $centroid[$j] * $centroid[$j];
        }
        $centroid = normaliseValue($centroid, sqrt($total));
        return $centroid;
    }

    function kMeans($data, $k)
    {
        $centroids = initialiseCentroids($data, $k);
        $mapping = array();

        while (true) {
            $new_mapping = assignCentroids($data, $centroids);
            $changed = false;
            foreach ($new_mapping as $documentID => $centroidID) {
                if (!isset($mapping[$documentID]) || $centroidID != $mapping[$documentID]) {
                    $mapping = $new_mapping;
                    $changed = true;
                    break;
                }
            }
            if (!$changed) {
                return formatResults($mapping, $data, $centroids);
            }
            $centroids = updateCentroids($mapping, $data, $k);
        }
    }

    function formatResults($mapping, $data, $centroids)
    {
        $result = array();
        $result['centroids'] = $centroids;
        foreach ($mapping as $documentID => $centroidID) {
            $result[$centroidID][] = implode(',', $data[$documentID]);
        }
        return $result;
    }

    function assignCentroids($data, $centroids)
    {
        $mapping = array();

        foreach ($data as $documentID => $document) {
            $minDist = 100;
            $minCentroid = null;
            foreach ($centroids as $centroidID => $centroid) {
                $dist = 0;
                foreach ($centroid as $dim => $value) {
                    $dist += abs($value - $document[$dim]);
                }
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $minCentroid = $centroidID;
                }
            }
            $mapping[$documentID] = $minCentroid;
        }

        return $mapping;
    }

    function updateCentroids($mapping, $data, $k)
    {
        $centroids = array();
        $counts = array_count_values($mapping);

        foreach ($mapping as $documentID => $centroidID) {
            foreach ($data[$documentID] as $dim => $value) {
                $centroids[$centroidID][$dim] += ($value / $counts[$centroidID]);
            }
        }

        if (count($centroids) < $k) {
            $centroids = array_merge($centroids, initialiseCentroids($data, $k - count($centroids)));
        }

        return $centroids;
    }

    function normaliseValue(array $vector, $total)
    {
        foreach ($vector as &$value) {
            $value = $value / $total;
        }
        return $vector;
    }

    // Return the dominant color
    return $result;
}