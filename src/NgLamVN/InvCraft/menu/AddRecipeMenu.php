<?php

namespace NgLamVN\InvCraft\menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use NgLamVN\InvCraft\Loader;
use NgLamVN\InvCraft\Recipe;
use pocketmine\item\Item;
use pocketmine\Player;

class AddRecipeMenu extends BaseMenu
{
    const PROTECTED_SLOT = [6, 7, 8, 15, 16, 17, 24, 25, 26, 33, 35, 42, 43, 44, 51, 52];
    /** @var string */
    public $recipe_name;

    public function __construct(Player $player, Loader $loader,string $recipe_name)
    {
        $this->recipe_name = $recipe_name;
        parent::__construct($player, $loader);
    }

    public function menu(Player $player)
    {
        $this->menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $this->menu->setName($this->getLoader()->getProvider()->getMessage("menu.add"));
        $this->menu->setListener(\Closure::fromCallable([$this, "MenuListener"]));
        $inv = $this->menu->getInventory();

        $ids = explode(":", $this->getLoader()->getProvider()->getMessage("menu.item"));
        $item = Item::get($ids[0], $ids[1]);
        for ($i = 0; $i <= 52; $i++)
        {
            if (in_array($i, self::PROTECTED_SLOT))
            {
                $inv->setItem($i, $item);
            }
        }
        $idsave = explode(":", $this->getLoader()->getProvider()->getMessage("menu.save.item"));
        $save = Item::get($idsave[0], $idsave[1])->setCustomName($this->getLoader()->getProvider()->getMessage("menu.save.name"));
        $inv->setItem(53, $save);

        $this->menu->send($player);
    }

    public function MenuListener(InvMenuTransaction $transaction)
    {
        if (in_array($transaction->getAction()->getSlot(), self::PROTECTED_SLOT))
        {
            return $transaction->discard();
        }
        if ($transaction->getAction()->getSlot() === 53)
        {
            $this->save();
            $transaction->getPlayer()->removeAllWindows();
            return $transaction->discard();
        }
        return $transaction->continue();
    }

    public function save()
    {
        $recipe_data = $this->makeRecipeData();
        $result = $this->menu->getInventory()->getItem(34);

        if ($result->getId() == Item::AIR)
        {
            $this->getPlayer()->sendMessage($this->getLoader()->getProvider()->getMessage("msg.missresult"));
            return;
        }
        $recipe = Recipe::makeRecipe($this->recipe_name, $recipe_data, $result);
        $this->getLoader()->setRecipe($recipe);
    }

    public function makeRecipeData(): array
    {
        $recipe_data = [];
        for ($i = 0; $i <= 53; $i++)
        {
            if (!in_array($i, self::PROTECTED_SLOT))
                if (($i !== 34) and ($i !== 53))
                {
                    $item = $this->menu->getInventory()->getItem($i);
                    array_push($recipe_data, $this->convert($item));
                }
        }
        return $recipe_data;
    }
}