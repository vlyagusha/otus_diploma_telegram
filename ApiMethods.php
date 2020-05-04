<?php

namespace Longman\TelegramBot\Commands\UserCommands;

class ApiMethods
{

    const GET_METHOD = 'GET';
    const POST_METHOD = 'POST';
    const DELETE_METHOD = 'DELETE';
    const METHOD_MOVIES = 'list';
    const METHOD_RECOMMENDATIONS = 'recommendations';
    const METHOD_PREFERENCE = 'preference';

    public static function getMoviesByNameFromApi($movie)
    {
        $movie = filter_var($movie, FILTER_SANITIZE_STRING);
        $getMoviesUrl = self::METHOD_MOVIES.'/'.$movie;
        $response =  ApiHandler::prepareApiRequest($movie, self::GET_METHOD, $getMoviesUrl);
        $movies = array_slice($response['movies'], 0, 6);

        return $movies;
    }

    private static function sendPreferencesToApi($data)
    {
        ApiHandler::prepareApiRequest($data, self::POST_METHOD, self::METHOD_PREFERENCE);
    }

    public static function deletePreferences($userId)
    {
        $data = [
            'user_id' => strval($userId),
        ];
        
        ApiHandler::prepareApiRequest($data, self::DELETE_METHOD, self::METHOD_PREFERENCE);
    }

    public static function getRecommendationsFromApi($userId, $movies)
    {
        $data = [
            'user_id' => strval($userId),
            'movies' => $movies
        ];

        self::sendPreferencesToApi($data);
        $recommendationsUrl = 'user/'.$userId.'/'.self::METHOD_RECOMMENDATIONS;
        $result = ApiHandler::prepareApiRequest(null, self::GET_METHOD, $recommendationsUrl);
        return $result['recommendations'];
    }
    
}
