@extends('errors.layout')

@section('code', '429')
@section('title', 'Trop de requêtes')
@section('icon', 'fas fa-hourglass-half')
@section('message', 'Trop de tentatives ont été effectuées en peu de temps. Veuillez patienter une minute avant de réessayer.')
