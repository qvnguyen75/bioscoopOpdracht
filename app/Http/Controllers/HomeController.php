<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller {

    private $freeSeatsForGroup = [];

    function index(){
        if($_POST){
            $this->reserveSeats($_POST['numberOfSeats']);
        };

        $data = [
            'totalSeats' => Seat::all(),
            'freeSeats' => Seat::all()->where('taken', false),
            'takenSeats' => Seat::all()->where('taken', true)
        ];
        return view('index', $data);
    }

    function reserveSeats($numberOfSeats){
        $freeSeats = Seat::all()->where('taken', false);

        if($numberOfSeats > count($freeSeats)){
            echo "null";
            return;
        }

        if($this->doesGroupFitTogether($numberOfSeats)){
            $key = 0;
            $index = 0;
            echo "Group fits, reserved seats: ";
            for($i=1; $i <= $numberOfSeats; $i++){
                $this->reserveSeat($this->freeSeatsForGroup[$key][$index]);
                echo $this->freeSeatsForGroup[$key][$index] . " ";
                $index++;
            }
            
        } else {
            echo "Group doesn't fit together reserved seat: ";
            $this->reserveFirstAvailableSeats($numberOfSeats);
        }
    }

    function doesGroupFitTogether($numberOfSeats){
        $seatIds = $this->getFreeSeatIds();
        $availableSeats = $this->getFreeSeatsAsArray($seatIds);
        foreach($availableSeats as $key => $value) {
            if(count($availableSeats[$key]) >= $numberOfSeats){
                $this->freeSeatsForGroup[] = $availableSeats[$key];
                return true;
            }
        } 
        return false;
    }

   
    function getFreeSeatsAsArray($seatIds){
        $freeSeatsArray = [];
        $key = 0;

        // verdeel seatids in aparte arrays
        $freeSeatsArray[$key] = [];
        foreach($seatIds as $id) {
            $nextId = next($seatIds);
            if($nextId - $id === 1 && !in_array($id, $freeSeatsArray[$key])){
                $freeSeatsArray[$key][] = $id;
            } 
            if ($id - $nextId === -1 && !in_array($nextId, $freeSeatsArray[$key])){
                $freeSeatsArray[$key][] = $nextId;
            }

            if($id + 1 !== $nextId){
                $key++;
                $freeSeatsArray[$key] = [];
            }
        }
        
        // verwijder lege arrays
        foreach($freeSeatsArray as $key => $value){
            if(count($freeSeatsArray[$key]) === 0){
                unset($freeSeatsArray[$key]);
            }
        }
    
        return $freeSeatsArray;
    }
   
    function getFreeSeatIds(){
        $seatIds = [];
        $freeSeats = Seat::all()->where('taken', false);
        foreach($freeSeats as $freeSeat){
            if(!$freeSeat->taken){
                $seatIds[] = $freeSeat->id;
            }
        }
        return $seatIds;
    }
  
    function reserveFirstAvailableSeats($numberOfSeats) {
        for($i = 1; $i <= $numberOfSeats; $i++){
            $seat = Seat::firstWhere('taken', 0);
            $seat->taken = 1;
            $seat->save();
            echo $seat->id . " ";
        }
    }

    function reserveSeat($seatId){
        $seat = Seat::find($seatId);
        $seat->taken = 1;
        $seat->save();
    }
}