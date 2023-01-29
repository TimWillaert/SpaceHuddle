<?php

namespace App\Domain\Participant\Type;

use ReflectionClass;

class DefaultNickname
{
    public const AINU = "Anonymous Ainu";
    public const APE = "Anonymous Ape";
    public const BAT = "Anonymous Bat";
    public const BEAR = "Anonymous Bear";
    public const BOAR = "Anonymous Boar";
    public const BEETLE = "Anonymous Beetle";
    public const BISON = "Anonymous Bison";
    public const BOBCAT = "Anonymous Bobcat";
    public const BONOBO = "Anonymous Bonobo";
    public const CAMEL = "Anonymous Camel";
    public const CARP = "Anonymous Carp";
    public const CAT = "Anonymous Cat";
    public const CICADA = "Anonymous Cicada";
    public const COBRA = "Anonymous Cobra";
    public const COW = "Anonymous Cow";
    public const CRAB = "Anonymous Crab";
    public const CRANE = "Anonymous Crane";
    public const CROW = "Anonymous Crow";
    public const DEER = "Anonymous Deer";
    public const DODO = "Anonymous Dodo";
    public const DOG = "Anonymous Dog";
    public const DOVE = "Anonymous Dove";
    public const DRAGON = "Anonymous Dragon";
    public const DUCK = "Anonymous Duck";
    public const EAGLE = "Anonymous Eagle";
    public const EEL = "Anonymous Eel";
    public const EGRET = "Anonymous Egret";
    public const ELK = "Anonymous Elk";
    public const EMU = "Anonymous Emu";
    public const FALCON = "Anonymous Falcon";
    public const FISH = "Anonymous Fish";
    public const FOX = "Anonymous Fox";
    public const FROG = "Anonymous Frog";
    public const GATOR = "Anonymous Gator";
    public const GECKO = "Anonymous Gecko";
    public const GERBIL = "Anonymous Gerbil";
    public const GHOST = "Anonymous Ghost";
    public const GOAT = "Anonymous Goat";
    public const GOOSE = "Anonymous Goose";
    public const HARE = "Anonymous Hare";
    public const HAWK = "Anonymous Hawk";
    public const HERON = "Anonymous Heron";
    public const HIPPO = "Anonymous Hippo";
    public const HORSE = "Anonymous Horse";
    public const HUMAN = "Anonymous Human";
    public const HUSKY = "Anonymous Husky";
    public const HYDRA = "Anonymous Hydra";
    public const HYENA = "Anonymous Hyena";
    public const IMPALA = "Anonymous Impala";
    public const JACKAL = "Anonymous Jackal";
    public const JAGUAR = "Anonymous Jaguar";
    public const KIWI = "Anonymous Kiwi";
    public const LEMUR = "Anonymous Lemur";
    public const LIGER = "Anonymous Liger";
    public const LION = "Anonymous Lion";
    public const LLAMA = "Anonymous Llama";
    public const LYNX = "Anonymous Lynx";
    public const MINK = "Anonymous Mink";
    public const MARMOT = "Anonymous Marmot";
    public const MONKEY = "Anonymous Monkey";
    public const MOOSE = "Anonymous Moose";
    public const MOTH = "Anonymous Moth";
    public const MOUSE = "Anonymous Mouse";
    public const NEWT = "Anonymous Newt";
    public const ORCA = "Anonymous Orca";
    public const OTTER = "Anonymous Otter";
    public const OWL = "Anonymous Owl";
    public const OX = "Anonymous Ox";
    public const PARROT = "Anonymous Parrot";
    public const PIG = "Anonymous Pig";
    public const PUMA = "Anonymous Puma";
    public const PYTHON = "Anonymous Python";
    public const QUAIL = "Anonymous Quail";
    public const RABBIT = "Anonymous Rabbit";
    public const RHINO = "Anonymous Rhino";
    public const ROBIN = "Anonymous Robin";
    public const SALMON = "Anonymous Salmon";
    public const SEAL = "Anonymous Seal";
    public const SERVAL = "Anonymous Serval";
    public const SHARK = "Anonymous Shark";
    public const SLOTH = "Anonymous Sloth";
    public const SPHYNX = "Anonymous Sphynx";
    public const SPIDER = "Anonymous Spider";
    public const SQUID = "Anonymous Squid";
    public const SWAN = "Anonymous Swan";
    public const TAPIR = "Anonymous Tapir";
    public const THRUSH = "Anonymous Thrush";
    public const TOAD = "Anonymous Toad";
    public const TURKEY = "Anonymous Turkey";
    public const TURTLE = "Anonymous Turtle";
    public const URIAL = "Anonymous Urial";
    public const VOLE = "Anonymous Vole";
    public const WALRUS = "Anonymous Walrus";
    public const WASP = "Anonymous Wasp";
    public const WEASEL = "Anonymous Weasel";
    public const WHALE = "Anonymous Whale";
    public const WOLF = "Anonymous Wolf";
    public const WREN = "Anonymous Wren";
    public const YAK = "Anonymous Yak";
    public const ZEBRA = "Anonymous Zebra";

    public static function getRandomValue(): string
    {
        $oClass = new ReflectionClass(__CLASS__);
        $cases = $oClass->getConstants();
        $keys = array_keys($cases);
        return $cases[$keys[rand(0, count($cases) - 1)]];
    }
}
