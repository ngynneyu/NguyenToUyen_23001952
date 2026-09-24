<?php
require_once 'Movie.php';

function findMovieById($movies, $id) {
    if (!is_array($movies) || empty($movies)) return null;
    foreach ($movies as $movie) {
        if ($movie instanceof Movie && $movie->getId() == $id) {
            return $movie;
        }
    }
    return null;
}

function getTotalRevenue($movies) {
    $total = 0;
    if (!is_array($movies) || empty($movies)) return $total;
    
    foreach ($movies as $movie) {
        if ($movie instanceof Movie) {
            $total += $movie->getRevenue();
        }
    }
    return $total;
}

function getBestSellingMovie($movies) {
    if (!is_array($movies) || empty($movies)) return null;
    
    $bestMovie = null;
    $maxSold = -1;

    foreach ($movies as $movie) {
        if ($movie instanceof Movie) {
            $sold = $movie->getSoldSeats();
            if ($sold > $maxSold) {
                $maxSold = $sold;
                $bestMovie = $movie;
            }
        }
    }
    return $bestMovie;
}
