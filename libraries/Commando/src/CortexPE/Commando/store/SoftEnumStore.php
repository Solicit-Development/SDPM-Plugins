<?php

declare(strict_types=1);

namespace CortexPE\Commando\store;

use pocketmine\Server;
use CortexPE\Commando\exception\CommandoException;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;

class SoftEnumStore
{
	/** @var CommandEnum[] */
	private static array $enums = [];

	public static function getEnumByName(string $name): ?CommandEnum
	{
		return self::$enums[$name] ?? null;
	}

	/**
	 * @return CommandEnum[]
	 */
	public static function getEnums(): array
	{
		return self::$enums;
	}

	public static function addEnum(CommandEnum $enum): void
	{
		self::$enums[$enum->getName()] = $enum;
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_ADD);
	}

	public static function updateEnum(string $enumName, array $values): void
	{
		if (self::getEnumByName($enumName) === null) {
			throw new CommandoException("Unknown enum named " . $enumName);
		}
		$enum = self::$enums[$enumName] = new CommandEnum($enumName, $values);
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_SET);
	}

	public static function removeEnum(string $enumName): void
	{
		if (($enum = self::getEnumByName($enumName)) === null) {
			throw new CommandoException("Unknown enum named " . $enumName);
		}
		unset(self::$enums[$enumName]);
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_REMOVE);
	}

	public static function broadcastSoftEnum(CommandEnum $enum, int $type): void
	{
		$pk = new UpdateSoftEnumPacket();
		$pk->enumName = $enum->getName();
		$pk->values = $enum->getValues();
		$pk->type = $type;
		self::broadcastPacket($pk);
	}

	private static function broadcastPacket(ClientboundPacket $pk): void
	{
		($sv = Server::getInstance())->broadcastPackets($sv->getOnlinePlayers(), [$pk]);
	}
}
