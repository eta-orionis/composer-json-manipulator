<?php

declare(strict_types=1);

namespace EtaOrionis\ComposerJsonManipulator;

use EtaOrionis\ComposerJsonManipulator\Helpers\JsonCleaner;
use EtaOrionis\ComposerJsonManipulator\Helpers\JsonInliner;
use EtaOrionis\ComposerJsonManipulator\Helpers\PackageSorter;
use EtaOrionis\ComposerJsonManipulator\Helpers\Section;

class ComposerJson
{
    /**
     * @var string
     */
    private const CLASSMAP_KEY = 'classmap';

    /**
     * @var string
     */
    private const PHP = 'php';

    private ?string $name = null;

    private ?string $description = null;

    /**
     * @var string[]
     */
    private array $keywords = [];

    private ?string $homepage = null;

    /**
     * @var string|string[]|null
     */
    private array|string|null $license = null;

    private ?string $minimumStability = null;

    private ?bool $preferStable = null;

    /**
     * @var mixed[]
     */
    private array $repositories = [];

    /**
     * @var array<string, string>
     */
    private array $require = [];

    /**
     * @var mixed[]
     */
    private array $autoload = [];

    /**
     * @var mixed[]
     */
    private array $extra = [];

    /**
     * @var array<string, string>
     */
    private array $requireDev = [];

    /**
     * @var mixed[]
     */
    private array $autoloadDev = [];

    /**
     * @var string[]
     */
    private array $orderedKeys = [];

    /**
     * @var array<string, string>
     */
    private array $replace = [];

    /**
     * @var array<string, string|string[]>
     */
    private array $scripts = [];

    /**
     * @var mixed[]
     */
    private array $config = [];

    /**
     * @var array<string, string>
     */
    private array $conflicts = [];

    /**
     * @var mixed[]
     */
    private array $bin = [];

    private ?string $type = null;

    /**
     * @var mixed[]
     */
    private array $authors = [];

    /**
     * @var array<string, string>
     */
    private array $scriptsDescriptions = [];

    /**
     * @var array<string, string>
     */
    private array $suggest = [];

    private ?string $version = null;

    /**
     * @var array<string, string>
     */
    private array $provide = [];


    private PackageSorter $composerPackageSorter;
    private JsonInliner $jsonInliner;
    private JsonCleaner $jsonCleaner;


    public function __construct()
    {
        $this->composerPackageSorter = new PackageSorter();
        $this->jsonCleaner = new JsonCleaner();
        $this->jsonInliner = new JsonInliner();
    }

    public static function fromString(string $jsonString): self
    {
        $jsonArray = json_decode($jsonString, true);
        return static::fromArray($jsonArray);
    }

    public static function fromFile(string $filePath): self
    {
        $jsonArray = static::load($filePath);

        $composerJson = static::fromArray($jsonArray);

        return $composerJson;
    }

    public static function blank(): self
    {
        return new ComposerJson();
    }

    /**
     * @param mixed[] $jsonArray
     */
    public static function fromArray(array $jsonArray): self
    {
        $composerJson = new ComposerJson();

        if (isset($jsonArray[Section::CONFIG])) {
            $composerJson->setConfig($jsonArray[Section::CONFIG]);
        }

        if (isset($jsonArray[Section::NAME])) {
            $composerJson->setName($jsonArray[Section::NAME]);
        }

        if (isset($jsonArray[Section::TYPE])) {
            $composerJson->setType($jsonArray[Section::TYPE]);
        }

        if (isset($jsonArray[Section::AUTHORS])) {
            $composerJson->setAuthors($jsonArray[Section::AUTHORS]);
        }

        if (isset($jsonArray[Section::DESCRIPTION])) {
            $composerJson->setDescription($jsonArray[Section::DESCRIPTION]);
        }

        if (isset($jsonArray[Section::KEYWORDS])) {
            $composerJson->setKeywords($jsonArray[Section::KEYWORDS]);
        }

        if (isset($jsonArray[Section::HOMEPAGE])) {
            $composerJson->setHomepage($jsonArray[Section::HOMEPAGE]);
        }

        if (isset($jsonArray[Section::LICENSE])) {
            $composerJson->setLicense($jsonArray[Section::LICENSE]);
        }

        if (isset($jsonArray[Section::BIN])) {
            $composerJson->setBin($jsonArray[Section::BIN]);
        }

        if (isset($jsonArray[Section::REQUIRE])) {
            $composerJson->setRequire($jsonArray[Section::REQUIRE]);
        }

        if (isset($jsonArray[Section::REQUIRE_DEV])) {
            $composerJson->setRequireDev($jsonArray[Section::REQUIRE_DEV]);
        }

        if (isset($jsonArray[Section::AUTOLOAD])) {
            $composerJson->setAutoload($jsonArray[Section::AUTOLOAD]);
        }

        if (isset($jsonArray[Section::AUTOLOAD_DEV])) {
            $composerJson->setAutoloadDev($jsonArray[Section::AUTOLOAD_DEV]);
        }

        if (isset($jsonArray[Section::REPLACE])) {
            $composerJson->setReplace($jsonArray[Section::REPLACE]);
        }

        if (isset($jsonArray[Section::EXTRA])) {
            $composerJson->setExtra($jsonArray[Section::EXTRA]);
        }

        if (isset($jsonArray[Section::SCRIPTS])) {
            $composerJson->setScripts($jsonArray[Section::SCRIPTS]);
        }

        if (isset($jsonArray[Section::SCRIPTS_DESCRIPTIONS])) {
            $composerJson->setScriptsDescriptions($jsonArray[Section::SCRIPTS_DESCRIPTIONS]);
        }

        if (isset($jsonArray[Section::SUGGEST])) {
            $composerJson->setSuggest($jsonArray[Section::SUGGEST]);
        }

        if (isset($jsonArray[Section::MINIMUM_STABILITY])) {
            $composerJson->setMinimumStability($jsonArray[Section::MINIMUM_STABILITY]);
        }

        if (isset($jsonArray[Section::PREFER_STABLE])) {
            $composerJson->setPreferStable($jsonArray[Section::PREFER_STABLE]);
        }

        if (isset($jsonArray[Section::CONFLICT])) {
            $composerJson->setConflicts($jsonArray[Section::CONFLICT]);
        }

        if (isset($jsonArray[Section::REPOSITORIES])) {
            $composerJson->setRepositories($jsonArray[Section::REPOSITORIES]);
        }

        if (isset($jsonArray[Section::VERSION])) {
            $composerJson->setVersion($jsonArray[Section::VERSION]);
        }

        if (isset($jsonArray[Section::PROVIDE])) {
            $composerJson->setProvide($jsonArray[Section::PROVIDE]);
        }

        $orderedKeys = array_keys($jsonArray);
        $composerJson->setOrderedKeys($orderedKeys);

        return $composerJson;
    }


    /**
     * @return array<string, mixed>
     */
    public static function load(string $filePath): array
    {
        $fileContent = file_get_contents($filePath);
        return json_decode($fileContent, true);
    }

    public function save(string $filePath): self
    {
        $jsonString = $this->toJsonString($this->getJsonArray());
        file_put_contents($filePath, $jsonString);
        return $this;
    }

    /**
     * @param mixed[] $json
     */
    public function toJsonString(array $json): string
    {
        // Empty arrays may lead to bad encoding since we can't be sure whether they need to be arrays or objects.
        $json = $this->jsonCleaner->removeEmptyKeysFromJsonArray($json);
        $jsonContent = json_encode($json, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);

        return $this->jsonInliner->inlineSections($jsonContent);
    }


    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param array<string, string> $require
     */
    public function setRequire(array $require): self
    {
        $this->require = $this->sortPackagesIfNeeded($require);
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getRequire(): array
    {
        return $this->require;
    }

    public function getRequirePhpVersion(): ?string
    {
        return $this->require[self::PHP] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function getRequireDev(): array
    {
        return $this->requireDev;
    }

    /**
     * @param array<string, string> $requireDev
     */
    public function setRequireDev(array $requireDev): self
    {
        $this->requireDev = $this->sortPackagesIfNeeded($requireDev);
        return $this;
    }

    /**
     * @param string[] $orderedKeys
     */
    public function setOrderedKeys(array $orderedKeys): self
    {
        $this->orderedKeys = $orderedKeys;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getOrderedKeys(): array
    {
        return $this->orderedKeys;
    }

    /**
     * @return mixed[]
     */
    public function getAutoload(): array
    {
        return $this->autoload;
    }

    /**
     * @return string[]
     */
    public function getAbsoluteAutoloadDirectories(): array
    {
        $autoloadDirectories = $this->getAutoloadDirectories();

        $absoluteAutoloadDirectories = [];

        foreach ($autoloadDirectories as $autoloadDirectory) {
            if (is_file($autoloadDirectory)) {
                // skip files
                continue;
            }

            $absoluteAutoloadDirectories[] = $this->resolveExistingAutoloadDirectory($autoloadDirectory);
        }

        return $absoluteAutoloadDirectories;
    }

    /**
     * @param mixed[] $autoload
     */
    public function setAutoload(array $autoload): self
    {
        $this->autoload = $autoload;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getAutoloadDev(): array
    {
        return $this->autoloadDev;
    }

    /**
     * @param mixed[] $autoloadDev
     */
    public function setAutoloadDev(array $autoloadDev): self
    {
        $this->autoloadDev = $autoloadDev;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    /**
     * @param mixed[] $repositories
     */
    public function setRepositories(array $repositories): self
    {
        $this->repositories = $repositories;
        return $this;
    }

    public function setMinimumStability(string $minimumStability): self
    {
        $this->minimumStability = $minimumStability;
        return $this;
    }

    public function removeMinimumStability(): self
    {
        $this->minimumStability = null;
        return $this;
    }

    public function getMinimumStability(): ?string
    {
        return $this->minimumStability;
    }

    public function getPreferStable(): ?bool
    {
        return $this->preferStable;
    }

    public function setPreferStable(bool $preferStable): self
    {
        $this->preferStable = $preferStable;
        return $this;
    }

    public function removePreferStable(): self
    {
        $this->preferStable = null;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @param mixed[] $extra
     */
    public function setExtra(array $extra): self
    {
        $this->extra = $extra;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getVendorName(): ?string
    {
        if ($this->name === null) {
            return null;
        }

        [$vendor] = explode('/', $this->name);
        return $vendor;
    }

    public function getShortName(): ?string
    {
        if ($this->name === null) {
            return null;
        }

        return $this->stringPartAfter($this->name, '/');
    }

    /**
     * @return array<string, string>
     */
    public function getReplace(): array
    {
        return $this->replace;
    }

    public function isReplacePackageSet(string $packageName): bool
    {
        return isset($this->replace[$packageName]);
    }

    /**
     * @param array<string, string> $replace
     */
    public function setReplace(array $replace): self
    {
        ksort($replace);

        $this->replace = $replace;
        return $this;
    }

    public function setReplacePackage(string $packageName, string $version): self
    {
        $this->replace[$packageName] = $version;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getJsonArray(): array
    {
        $array = [
            Section::NAME => $this->name,
            Section::DESCRIPTION => $this->description,
            Section::KEYWORDS => $this->keywords,
            Section::HOMEPAGE => $this->homepage,
            Section::LICENSE => $this->license,
            Section::AUTHORS => $this->authors,
            Section::TYPE => $this->type,
            Section::REQUIRE => $this->require,
            Section::REQUIRE_DEV => $this->requireDev,
            Section::AUTOLOAD => $this->autoload,
            Section::AUTOLOAD_DEV => $this->autoloadDev,
            Section::REPOSITORIES => $this->repositories,
            Section::EXTRA => $this->extra,
            Section::BIN => $this->bin,
            Section::SCRIPTS => $this->scripts,
            Section::SCRIPTS_DESCRIPTIONS => $this->scriptsDescriptions,
            Section::SUGGEST => $this->suggest,
            Section::CONFIG => $this->config,
            Section::REPLACE => $this->replace,
            Section::CONFLICT => $this->conflicts,
            Section::PROVIDE => $this->provide,
            Section::VERSION => $this->version,
        ];

        if ($this->minimumStability !== null) {
            $array[Section::MINIMUM_STABILITY] = $this->minimumStability;
            $this->moveValueToBack(Section::MINIMUM_STABILITY);
        }

        if ($this->preferStable !== null) {
            $array[Section::PREFER_STABLE] = $this->preferStable;
            $this->moveValueToBack(Section::PREFER_STABLE);
        }

        // echo '595';
        // var_dump(isset($array[Section::REQUIRE_DEV]));
        // die;

        return $this->sortItemsByOrderedListOfKeys($array, $this->orderedKeys);
    }

    /**
     * @param array<string, string|string[]> $scripts
     */
    public function setScripts(array $scripts): self
    {
        $this->scripts = $scripts;
        return $this;
    }

    /**
     * @param mixed[] $config
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string[] $keywords
     */
    public function setKeywords(array $keywords): self
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setHomepage(string $homepage): self
    {
        $this->homepage = $homepage;
        return $this;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    /**
     * @param string|string[]|null $license
     */
    public function setLicense(string | array | null $license): self
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @return string|string[]|null
     */
    public function getLicense(): string|array|null
    {
        return $this->license;
    }

    /**
     * @param mixed[] $authors
     */
    public function setAuthors(array $authors): self
    {
        $this->authors = $authors;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function hasPackage(string $packageName): bool
    {
        if ($this->hasRequiredPackage($packageName)) {
            return true;
        }

        return $this->hasRequiredDevPackage($packageName);
    }

    public function hasRequiredPackage(string $packageName): bool
    {
        return isset($this->require[$packageName]);
    }

    public function hasRequiredDevPackage(string $packageName): bool
    {
        return isset($this->requireDev[$packageName]);
    }

    public function addRequiredPackage(string $packageName, string $version): self
    {
        if (!$this->hasPackage($packageName)) {
            $this->require[$packageName] = $version;
            $this->require = $this->sortPackagesIfNeeded($this->require);
        }
        return $this;
    }

    public function addRequiredDevPackage(string $packageName, string $version): self
    {
        if (!$this->hasPackage($packageName)) {
            $this->requireDev[$packageName] = $version;
            $this->requireDev = $this->sortPackagesIfNeeded($this->requireDev);
        }
        return $this;
    }

    public function changePackageVersion(string $packageName, string $version): self
    {
        if ($this->hasRequiredPackage($packageName)) {
            $this->require[$packageName] = $version;
        }

        if ($this->hasRequiredDevPackage($packageName)) {
            $this->requireDev[$packageName] = $version;
        }
        return $this;
    }

    public function movePackageToRequire(string $packageName): self
    {
        if (!$this->hasRequiredDevPackage($packageName)) {
            return $this;
        }

        $version = $this->requireDev[$packageName];
        $this->removePackage($packageName);
        $this->addRequiredPackage($packageName, $version);
        return $this;
    }

    public function movePackageToRequireDev(string $packageName): self
    {
        if (!$this->hasRequiredPackage($packageName)) {
            return $this;
        }

        $version = $this->require[$packageName];
        $this->removePackage($packageName);
        $this->addRequiredDevPackage($packageName, $version);
        return $this;
    }

    public function removePackage(string $packageName): self
    {
        unset($this->require[$packageName], $this->requireDev[$packageName]);
        return $this;
    }

    public function replacePackage(string $oldPackageName, string $newPackageName, string $targetVersion): self
    {
        if ($this->hasRequiredPackage($oldPackageName)) {
            unset($this->require[$oldPackageName]);
            $this->addRequiredPackage($newPackageName, $targetVersion);
        }

        if ($this->hasRequiredDevPackage($oldPackageName)) {
            unset($this->requireDev[$oldPackageName]);
            $this->addRequiredDevPackage($newPackageName, $targetVersion);
        }
        return $this;
    }

    /**
     * @param array<string, string> $conflicts
     */
    public function setConflicts(array $conflicts): self
    {
        $this->conflicts = $conflicts;
        return $this;
    }

    /**
     * @param mixed[] $bin
     */
    public function setBin(array $bin): self
    {
        $this->bin = $bin;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getBin(): array
    {
        return $this->bin;
    }

    /**
     * @return string[]
     */
    public function getPsr4AndClassmapDirectories(): array
    {
        $psr4Directories = array_values($this->autoload['psr-4'] ?? []);
        $classmapDirectories = $this->autoload['classmap'] ?? [];

        return array_merge($psr4Directories, $classmapDirectories);
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @return array<string, string>
     */
    public function getScriptsDescriptions(): array
    {
        return $this->scriptsDescriptions;
    }

    /**
     * @return array<string, string>
     */
    public function getSuggest(): array
    {
        return $this->suggest;
    }

    /**
     * @return string[]
     */
    public function getAllClassmaps(): array
    {
        $autoloadClassmaps = $this->autoload[self::CLASSMAP_KEY] ?? [];
        $autoloadDevClassmaps = $this->autoloadDev[self::CLASSMAP_KEY] ?? [];

        return array_merge($autoloadClassmaps, $autoloadDevClassmaps);
    }

    /**
     * @return array<string, string>
     */
    public function getConflicts(): array
    {
        return $this->conflicts;
    }

    /**
     * @api
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getAutoloadDirectories(): array
    {
        $autoloadDirectories = array_merge(
            $this->getPsr4AndClassmapDirectories(),
            $this->getPsr4AndClassmapDevDirectories()
        );

        return $this->flattenArray($autoloadDirectories);
    }

    /**
     * @return string[]
     */
    public function getPsr4AndClassmapDevDirectories(): array
    {
        $psr4Directories = array_values($this->autoloadDev['psr-4'] ?? []);
        $classmapDirectories = $this->autoloadDev['classmap'] ?? [];

        return array_merge($psr4Directories, $classmapDirectories);
    }

    /**
     * @param array<string, string> $scriptsDescriptions
     */
    public function setScriptsDescriptions(array $scriptsDescriptions): self
    {
        $this->scriptsDescriptions = $scriptsDescriptions;
        return $this;
    }

    /**
     * @param array<string, string> $suggest
     */
    public function setSuggest(array $suggest): self
    {
        $this->suggest = $suggest;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDuplicatedRequirePackages(): array
    {
        $requiredPackageNames = $this->require;
        $requiredDevPackageNames = $this->requireDev;

        return array_intersect($requiredPackageNames, $requiredDevPackageNames);
    }

    /**
     * @return string[]
     */
    public function getRequirePackageNames(): array
    {
        return array_keys($this->require);
    }

    /**
     * @return array<string, string>
     */
    public function getProvide(): array
    {
        return $this->provide;
    }

    public function isProvidePackageSet(string $packageName): bool
    {
        return isset($this->provide[$packageName]);
    }

    /**
     * @param array<string, string> $provide
     */
    public function setProvide(array $provide): self
    {
        ksort($provide);

        $this->provide = $provide;
        return $this;
    }

    public function setProvidePackage(string $packageName, string $version): self
    {
        $this->provide[$packageName] = $version;
        return $this;
    }

    /**
     * @param Section::* $valueName
     */
    private function moveValueToBack(string $valueName): self
    {
        $key = array_search($valueName, $this->orderedKeys, true);
        if ($key !== false) {
            unset($this->orderedKeys[$key]);
        }

        $this->orderedKeys[] = $valueName;
        return $this;
    }

    /**
     * 2. sort item by prescribed key order
     *
     * @see https://www.designcise.com/web/tutorial/how-to-sort-an-array-by-keys-based-on-order-in-a-secondary-array-in-php
     * @param array<string, mixed> $contentItems
     * @param string[] $orderedVisibleItems
     * @return mixed[]
     */
    private function sortItemsByOrderedListOfKeys(array $contentItems, array $orderedVisibleItems): array
    {

        uksort($contentItems, function ($firstContentItem, $secondContentItem) use ($orderedVisibleItems): int {
            $firstItemPosition = $this->findPosition($firstContentItem, $orderedVisibleItems);
            $secondItemPosition = $this->findPosition($secondContentItem, $orderedVisibleItems);

            if ($firstItemPosition === false) {
                // new item, put in the back
                return -1;
            }

            if ($secondItemPosition === false) {
                // new item, put in the back
                return -1;
            }

            return $firstItemPosition <=> $secondItemPosition;
        });


        return $contentItems;
    }

    private function resolveExistingAutoloadDirectory(string $autoloadDirectory, ?string $base_path = null): string
    {

        $filePathCandidates = [
            ($base_path ?? getcwd()) . DIRECTORY_SEPARATOR . $autoloadDirectory,
            // mostly tests
            getcwd() . DIRECTORY_SEPARATOR . $autoloadDirectory,
        ];

        foreach ($filePathCandidates as $filePathCandidate) {
            if (file_exists($filePathCandidate)) {
                return $filePathCandidate;
            }
        }

        return $autoloadDirectory;
    }

    /**
     * @param array<string, string> $packages
     * @return array<string, string>
     */
    private function sortPackagesIfNeeded(array $packages): array
    {
        $sortPackages = $this->config['sort-packages'] ?? false;
        if ($sortPackages) {
            return $this->composerPackageSorter->sortPackages($packages);
        }

        return $packages;
    }

    /**
     * @param string[] $items
     */
    private function findPosition(string $key, array $items): int | string | bool
    {
        return array_search($key, $items, true);
    }

    private function stringPartAfter($s, $char): string
    {
        if (($pos = strpos($s, $char)) !== false) {
            return substr($s, $pos + 1);
        }

        return '';
    }

    private function flattenArray($a): array
    {
        $result = [];
        foreach ($a as $element) {
            if (is_array($element)) {
                $result = [...$result, ...$this->flattenArray($element)];
            } else {
                $result[] = $element;
            }
        }
        return $result;
    }
}
