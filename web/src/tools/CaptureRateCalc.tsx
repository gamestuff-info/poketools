import React, {ChangeEvent, useCallback, useContext, useEffect, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import useVersionRedirect from '../common/components/useVersionRedirect';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import Loading from '../common/components/Loading';
import {Alert, Col, Form, Row, Table} from 'react-bootstrap';
import PokemonSelect from '../pokemon/PokemonSelect';
import ReactSelect, {ActionMeta, OptionTypeBase} from 'react-select';
import {Props as ReactSelectProps} from 'react-select/src/Select';
import RepeatedIcon from '../common/components/RepeatedIcon';
import {faArrowUp} from '@fortawesome/free-solid-svg-icons';
import ItemLabel from '../item/ItemLabel';
import PktMarkdown from '../common/components/PktMarkdown';
import {DateTime, Interval} from 'luxon';
import setPageTitle from '../common/setPageTitle';

interface CaptureRateCalcDataState {
    loadedForVersion?: number
    balls?: Array<ApiRecord.Item.ItemInVersionGroup.CaptureRate>
    loadingBalls: boolean
    allPokemon?: Array<ApiRecord.Pokemon.Pokemon.CaptureRate>
    loadingPokemon: boolean
    methods?: Array<ApiRecord.Pokemon.EncounterMethod>
    loadingMethods: boolean
    genders?: Array<ApiRecord.Pokemon.Gender>
    loadingGenders: boolean
    timesOfDay?: Array<ApiRecord.TimeOfDay>
    loadingTimesOfDay: boolean
}

interface CaptureRateCalcUserState {
    pokemonAttacking: ApiRecord.Pokemon.Pokemon.CaptureRate | null
    pokemonDefending: ApiRecord.Pokemon.Pokemon.CaptureRate | null
    method: ApiRecord.Pokemon.EncounterMethod | null
    levelAttacking: number
    levelDefending: number
    genderAttacking: ApiRecord.Pokemon.Gender | null
    genderDefending: ApiRecord.Pokemon.Gender | null
    timeOfDay: ApiRecord.TimeOfDay | null
    inDarkGrass: boolean
    pokedexCount: number
    capturePower: number
    hp: number
}

interface CaptureRateCalcState extends CaptureRateCalcDataState, CaptureRateCalcUserState {
}

const defaultUserState: CaptureRateCalcUserState = {
    pokemonAttacking: null,
    pokemonDefending: null,
    method: null,
    levelAttacking: 5,
    levelDefending: 5,
    genderAttacking: null,
    genderDefending: null,
    timeOfDay: null,
    inDarkGrass: false,
    pokedexCount: 0,
    capturePower: 0,
    hp: 100,
};

export default function CaptureRateCalc(props: {}) {
    // Setup
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: CaptureRateCalcState, newState: Partial<CaptureRateCalcState>) => ({...state, ...newState}), Object.assign({
        loadingBalls: false,
        loadingPokemon: false,
        loadingMethods: false,
        loadingGenders: false,
        loadingTimesOfDay: false,
    }, defaultUserState) as CaptureRateCalcState);
    const {balls, allPokemon, methods, genders, timesOfDay} = state;
    setPageTitle('Capture Rate Calculator');

    // Reset
    if (state.loadedForVersion !== undefined && state.loadedForVersion !== currentVersion.id) {
        setState(Object.assign({
            loadedForVersion: undefined,
            balls: undefined,
            allPokemon: undefined,
            methods: undefined,
            genders: undefined,
            timesOfDay: undefined,
        }, defaultUserState));
    }

    // Slugs, for creating param gates
    const ballSlugs = useMemo(() => new Set(balls ? balls.map(ball => ball.slug) : []), [balls]);
    const methodSlugs = useMemo(() => new Set(methods ? methods.map(method => method.slug) : []), [methods]);
    /** Turn various inputs on/off depending on context */
    const paramGates = useMemo(() => ({
        capturePower: [5, 6].includes(currentVersion.generationNumber),
        // Several versions have dark grass, but it only affects capture rate in Gen 5.
        darkGrass: currentVersion.generationNumber === 5 && methodSlugs.has('dark-grass'),
        gender: ballSlugs.has('love-ball'),
        levelAttacking: ballSlugs.has('level-ball'),
        levelDefending: ballSlugs.has('level-ball') || ballSlugs.has('nest-ball'),
        method: ballSlugs.has('lure-ball') || ballSlugs.has('dive-ball'),
        pokemonAttacking: ballSlugs.has('love-ball'),
        time: timesOfDay !== undefined && timesOfDay.length > 0 && ballSlugs.has('dusk-ball'),
    }), [currentVersion, methodSlugs, ballSlugs, timesOfDay]);

    // Save/load the state from the URL
    const stateFromUrl = useCallback(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const newState: Partial<CaptureRateCalcUserState> = {};

        if (urlParams.has('pokemonAttacking')) {
            const pokemonAttacking = findBySlug(urlParams.get('pokemonAttacking') as string, allPokemon ?? []);
            if (pokemonAttacking !== null) {
                newState.pokemonAttacking = pokemonAttacking;
            }
        }
        if (urlParams.has('pokemonDefending')) {
            const pokemonDefending = findBySlug(urlParams.get('pokemonDefending') as string, allPokemon ?? []);
            if (pokemonDefending !== null) {
                newState.pokemonDefending = pokemonDefending;
            }
        }
        if (paramGates.method && urlParams.has('method')) {
            const method = findBySlug(urlParams.get('method') as string, methods ?? []);
            if (method !== null) {
                newState.method = method;
            }
        }
        if (paramGates.levelAttacking && urlParams.has('levelAttacking')) {
            const levelAttacking = parseInt(urlParams.get('levelAttacking') as string);
            if (!isNaN(levelAttacking) && levelAttacking > 0 && levelAttacking <= 100) {
                newState.levelAttacking = levelAttacking;
            }
        }
        if (paramGates.levelDefending && urlParams.has('levelDefending')) {
            const levelDefending = parseInt(urlParams.get('levelDefending') as string);
            if (!isNaN(levelDefending) && levelDefending > 0 && levelDefending <= 100) {
                newState.levelDefending = levelDefending;
            }
        }
        if (paramGates.gender && urlParams.has('genderAttacking')) {
            const genderAttacking = findBySlug(urlParams.get('genderAttacking') as string, genders ?? []);
            if (genderAttacking !== null) {
                newState.genderAttacking = genderAttacking;
            }
        }
        if (paramGates.gender && urlParams.has('genderDefending')) {
            const genderDefending = findBySlug(urlParams.get('genderDefending') as string, genders ?? []);
            if (genderDefending !== null) {
                newState.genderDefending = genderDefending;
            }
        }
        if (urlParams.has('timeOfDay')) {
            const timeOfDay = findBySlug(urlParams.get('timeOfDay') as string, timesOfDay ?? []);
            if (timeOfDay !== null) {
                newState.timeOfDay = timeOfDay;
            }
        }
        if (urlParams.has('inDarkGrass')) {
            const inDarkGrass = parseInt(urlParams.get('inDarkGrass') as string);
            if (!isNaN(inDarkGrass)) {
                newState.inDarkGrass = !!inDarkGrass;
            }
        }
        if (urlParams.has('pokedexCount')) {
            const pokedexCount = parseInt(urlParams.get('pokedexCount') as string);
            if (!isNaN(pokedexCount) && pokedexCount >= 0) {
                newState.pokedexCount = pokedexCount;
            }
        }
        if (urlParams.has('capturePower')) {
            const capturePower = parseInt(urlParams.get('capturePower') as string);
            if (!isNaN(capturePower) && capturePower >= 0 && capturePower <= 3) {
                newState.capturePower = capturePower;
            }
        }
        if (urlParams.has('hp')) {
            const hp = parseInt(urlParams.get('hp') as string);
            if (!isNaN(hp) && hp > 0 && hp <= 100) {
                newState.hp = hp;
            }
        }

        return newState;
    }, [paramGates, allPokemon, methods, genders, timesOfDay]);
    useEffect(() => {
        if (allPokemon && methods && genders && timesOfDay) {
            setState(stateFromUrl());
        }
    }, [allPokemon, methods, genders, timesOfDay, stateFromUrl]);

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Load
    if (!state.loadingBalls && balls === undefined) {
        fetchBalls(currentVersion).then(newBalls => {
            // Remove unhelpful balls
            const unhelpfulBalls = ['cherish-ball', 'master-ball', 'park-ball'];
            if (currentVersion.generationNumber < 8) {
                // The dream ball has a very different effect in Gen 8
                unhelpfulBalls.push('dream-ball');
            }
            newBalls = newBalls.filter(ball => !unhelpfulBalls.includes(ball.slug));
            setState({
                balls: newBalls,
                loadingBalls: false,
                loadedForVersion: currentVersion.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading balls.'}]);
        });
        setState({loadingBalls: true});
    }
    if (!state.loadingPokemon && allPokemon === undefined) {
        fetchPokemon(currentVersion).then(newPokemon => {
            setState({
                allPokemon: newPokemon,
                loadingPokemon: false,
                loadedForVersion: currentVersion.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Pokémon.'}]);
        });
        setState({loadingPokemon: true});
    }
    if (!state.loadingMethods && methods === undefined) {
        fetchEncounterMethods(currentVersion).then(newMethods => {
            setState({
                methods: newMethods,
                loadingMethods: false,
                loadedForVersion: currentVersion.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading encounter methods.'}]);
        });
        setState({loadingMethods: true});
    }
    if (!state.loadingGenders && genders === undefined) {
        if (currentVersion.featureSlugs.includes('gender')) {
            fetchGenders(currentVersion).then(newGenders => {
                setState({
                    genders: newGenders,
                    loadingGenders: false,
                    loadedForVersion: currentVersion.id,
                });
            }).catch((error: AxiosError) => {
                console.log(error.message);
                setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading genders.'}]);
            });
            setState({loadingGenders: true});
        } else {
            // This version does not have genders
            setState({
                genders: [],
                loadingGenders: false,
                loadedForVersion: currentVersion.id,
            });
        }
    }
    if (!state.loadingTimesOfDay && timesOfDay === undefined) {
        if (currentVersion.featureSlugs.includes('time')) {
            fetchTimesOfDay(currentVersion).then(newTimesOfDay => {
                const newState: Partial<CaptureRateCalcState> = {
                    timesOfDay: newTimesOfDay,
                    loadingTimesOfDay: false,
                    loadedForVersion: currentVersion.id,
                };
                if (state.timeOfDay === null) {
                    // Set the selected time of day to the current time.
                    const now = DateTime.now();
                    for (const checkTimeOfDay of newTimesOfDay) {
                        const checkInterval = Interval.fromDateTimes(
                            DateTime.fromISO(checkTimeOfDay.startsIso8601),
                            DateTime.fromISO(checkTimeOfDay.endsIso8601)
                        );
                        if (checkInterval.contains(now)) {
                            newState.timeOfDay = checkTimeOfDay;
                            break;
                        }
                    }
                }
                setState(newState);
            }).catch((error: AxiosError) => {
                console.log(error.message);
                setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading times of day.'}]);
            });
            setState({loadingTimesOfDay: true});
        } else {
            // Since this version does not have times, don't bother with a request.
            setState({
                timesOfDay: [],
                loadingTimesOfDay: false,
                loadedForVersion: currentVersion.id,
            });
        }
    }

    return (
        <div>
            <h1>
                Capture Rate
            </h1>
            {(state.loadingBalls || state.loadingPokemon || state.loadingMethods || state.loadingTimesOfDay) && (
                <Loading label="Loading a lot of data..."/>
            )}
            {balls && allPokemon && methods && genders && timesOfDay && (
                <>
                    <Form>
                        {paramGates.pokemonAttacking && (
                            <Form.Group as={Row}>
                                <Form.Label column sm={2} htmlFor="pokemonAttacking">
                                    Your Pokémon
                                </Form.Label>
                                <Col>
                                    <PokemonSelect name="pokemonAttacking"
                                                   value={state.pokemonAttacking}
                                                   required
                                                   onChange={
                                                       (value: ApiRecord.Pokemon.Pokemon.CaptureRate | null, action: ActionMeta<ApiRecord.Pokemon.Pokemon.CaptureRate>) =>
                                                           handleSelectChange(value, action, (newValue) => setState({pokemonAttacking: newValue}))
                                                   }
                                                   allPokemon={allPokemon}
                                    />
                                </Col>
                            </Form.Group>
                        )}
                        <Form.Group as={Row}>
                            <Form.Label column sm={2} htmlFor="pokemonDefending">
                                Opponent Pokémon
                            </Form.Label>
                            <Col>
                                <PokemonSelect name="pokemonDefending"
                                               value={state.pokemonDefending}
                                               required
                                               onChange={
                                                   (value: ApiRecord.Pokemon.Pokemon.CaptureRate | null, action: ActionMeta<ApiRecord.Pokemon.Pokemon.CaptureRate>) =>
                                                       handleSelectChange(value, action, (newValue) => setState({pokemonDefending: newValue}))
                                               }
                                               allPokemon={allPokemon}
                                />
                            </Col>
                        </Form.Group>
                        {paramGates.method && (
                            <Form.Group as={Row}>
                                <Form.Label column sm={2} htmlFor="method">
                                    Method
                                </Form.Label>
                                <Col>
                                    <MethodSelect name="method"
                                                  value={state.method}
                                                  required
                                                  methods={methods}
                                                  onChange={
                                                      (value: ApiRecord.Pokemon.EncounterMethod, action: ActionMeta<ApiRecord.Pokemon.EncounterMethod>) =>
                                                          handleSelectChange(value, action, (newValue) => setState({method: newValue}))
                                                  }
                                    />
                                </Col>
                            </Form.Group>
                        )}
                        {paramGates.levelAttacking && (
                            <Form.Group as={Row}>
                                <Form.Label column sm={2} htmlFor="levelAttacking">
                                    Your Level
                                </Form.Label>
                                <Col>
                                    <LevelField name="levelAttacking"
                                                value={state.levelAttacking}
                                                onChange={
                                                    (e: ChangeEvent<HTMLInputElement>) =>
                                                        handleNumberChange(e.target.value, 1, 100, (newVal) => setState({levelAttacking: newVal}))
                                                }
                                    />
                                </Col>
                            </Form.Group>
                        )}
                        {paramGates.levelDefending && (
                            <Form.Group as={Row}>
                                <Form.Label column sm={2} htmlFor="levelDefending">
                                    Opponent's Level
                                </Form.Label>
                                <Col>
                                    <LevelField name="levelDefending"
                                                value={state.levelDefending}
                                                onChange={
                                                    (e: ChangeEvent<HTMLInputElement>) =>
                                                        handleNumberChange(e.target.value, 1, 100, (newVal) => setState({levelDefending: newVal}))
                                                }
                                    />
                                </Col>
                            </Form.Group>
                        )}
                        {paramGates.gender && (
                            <>
                                <Form.Group as={Row}>
                                    <Form.Label column sm={2} htmlFor="genderAttacking">
                                        Your Gender
                                    </Form.Label>
                                    <Col>
                                        <GenderSelect name="genderAttacking"
                                                      value={state.genderAttacking}
                                                      required
                                                      genders={genders}
                                                      onChange={
                                                          (value: ApiRecord.Pokemon.Gender, action: ActionMeta<ApiRecord.Pokemon.Gender>) =>
                                                              handleSelectChange(value, action, (newValue) => setState({genderAttacking: newValue}))
                                                      }
                                        />
                                    </Col>
                                </Form.Group>
                                <Form.Group as={Row}>
                                    <Form.Label column sm={2} htmlFor="genderDefending">
                                        Opponent's Gender
                                    </Form.Label>
                                    <Col>
                                        <GenderSelect name="genderDefending"
                                                      value={state.genderDefending}
                                                      required
                                                      genders={genders}
                                                      onChange={
                                                          (value: ApiRecord.Pokemon.Gender, action: ActionMeta<ApiRecord.Pokemon.Gender>) =>
                                                              handleSelectChange(value, action, (newValue) => setState({genderDefending: newValue}))
                                                      }
                                        />
                                    </Col>
                                </Form.Group>
                            </>
                        )}
                        {paramGates.time && (
                            <Form.Group as={Row}>
                                <Form.Label column sm={2} htmlFor="timeOfDay">
                                    Time
                                </Form.Label>
                                <Col>
                                    <TimeSelect name="timeOfDay"
                                                value={state.timeOfDay}
                                                required
                                                timesOfDay={timesOfDay}
                                                onChange={
                                                    (value: ApiRecord.TimeOfDay, action: ActionMeta<ApiRecord.TimeOfDay>) =>
                                                        handleSelectChange(value, action, (newValue) => setState({timeOfDay: newValue}))
                                                }
                                    />
                                </Col>
                            </Form.Group>
                        )}
                        {paramGates.darkGrass && (
                            <>
                                <Form.Group as={Row}>
                                    <Form.Label column sm={2} htmlFor="inDarkGrass">
                                        In Dark Grass
                                    </Form.Label>
                                    <Col className="d-flex align-items-center">
                                        <Form.Check name="inDarkGrass"
                                                    checked={state.inDarkGrass}
                                                    onChange={
                                                        (e: ChangeEvent<HTMLInputElement>) => setState({inDarkGrass: e.target.checked})
                                                    }
                                        />
                                    </Col>
                                </Form.Group>
                                <Form.Group as={Row}>
                                    <Form.Label column sm={2} htmlFor="pokedexCount">
                                        Pokédex Count
                                    </Form.Label>
                                    <Col>
                                        <Form.Control type="number"
                                                      name="pokedexCount"
                                                      min={0}
                                                      required
                                                      value={state.pokedexCount}
                                                      onChange={
                                                          (e: ChangeEvent<HTMLInputElement>) => handleNumberChange(e.target.value, 0, null, (newValue) => setState({pokedexCount: newValue}))
                                                      }
                                        />
                                    </Col>
                                </Form.Group>
                            </>
                        )}
                        {paramGates.capturePower && (
                            <Form.Group as={Row}>
                                <Form.Label column sm={2} htmlFor="capturePower">
                                    Capture Power
                                </Form.Label>
                                <Col className="d-flex align-items-center">
                                    {[0, 1, 2, 3].map(powerLevel => (
                                        <Form.Check key={powerLevel}
                                                    name="capturePower"
                                                    type="radio"
                                                    value={powerLevel}
                                                    inline
                                                    label={<CapturePowerLabel level={powerLevel}/>}
                                                    checked={state.capturePower === powerLevel}
                                                    onChange={() => setState({capturePower: powerLevel})}
                                        />
                                    ))}
                                </Col>
                            </Form.Group>
                        )}
                        <Form.Group as={Row}>
                            <Form.Label column sm={2} htmlFor="hp">
                                HP
                            </Form.Label>
                            <Col className="d-flex align-items-center">
                                <div className="d-flex w-100">
                                    <Form.Control type="range"
                                                  min={1}
                                                  max={100}
                                                  value={state.hp}
                                                  onChange={
                                                      (e: ChangeEvent<HTMLInputElement>) => handleNumberChange(e.target.value, 0, null, (newValue) => setState({hp: newValue}))
                                                  }
                                    />
                                    <div className="ml-2">{state.hp}%</div>
                                </div>
                            </Col>
                        </Form.Group>
                    </Form>
                    <CaptureRateTable {...state} balls={balls}/>
                </>
            )}
        </div>
    );
}

interface MethodSelectProps extends Omit<ReactSelectProps<ApiRecord.Pokemon.EncounterMethod>, 'options' | 'components'> {
    methods: Array<ApiRecord.Pokemon.EncounterMethod>
}

function MethodSelect(props: MethodSelectProps) {
    const {methods} = props;
    const getOptionLabel = useCallback((option: ApiRecord.Pokemon.EncounterMethod) => option.name, []);
    const getOptionValue = useCallback((option: ApiRecord.Pokemon.EncounterMethod) => option.slug, []);

    return (
        <ReactSelect {...props}
                     options={methods}
                     getOptionLabel={getOptionLabel}
                     getOptionValue={getOptionValue}
        />
    );
}

interface GenderSelectProps extends Omit<ReactSelectProps<ApiRecord.Pokemon.EncounterMethod>, 'options' | 'components'> {
    genders: Array<ApiRecord.Pokemon.Gender>
}

function GenderSelect(props: GenderSelectProps) {
    const {genders} = props;
    const getOptionLabel = useCallback((option: ApiRecord.Pokemon.Gender) => option.name, []);
    const getOptionValue = useCallback((option: ApiRecord.Pokemon.Gender) => option.slug, []);

    return (
        <ReactSelect {...props}
                     options={genders}
                     getOptionLabel={getOptionLabel}
                     getOptionValue={getOptionValue}
        />
    );
}

interface TimeSelectProps extends Omit<ReactSelectProps<ApiRecord.Pokemon.EncounterMethod>, 'options' | 'components'> {
    timesOfDay: Array<ApiRecord.TimeOfDay>
}

function TimeSelect(props: TimeSelectProps) {
    const {timesOfDay} = props;
    const getOptionLabel = useCallback((option: ApiRecord.TimeOfDay) => option.name, []);
    const getOptionValue = useCallback((option: ApiRecord.TimeOfDay) => option.slug, []);

    return (
        <ReactSelect {...props}
                     options={timesOfDay}
                     getOptionLabel={getOptionLabel}
                     getOptionValue={getOptionValue}
        />
    );
}

function LevelField(props: Record<string, any>) {
    return (
        <Form.Control type="number"
                      min={1}
                      max={100}
                      required
                      {...props}
        />
    );
}

function CapturePowerLabel(props: { level: number }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    if (props.level === 0) {
        return (<span>None</span>);
    } else if (currentVersion.generationNumber === 5) {
        return (
            <>
                <RepeatedIcon count={props.level} icon={faArrowUp}/>
                <span className="sr-only">{props.level}</span>
            </>
        );
    }

    return (
        <span>Lv. {props.level}</span>
    );
}

function handleSelectChange<OptionT extends OptionTypeBase>(value: OptionT | null, action: ActionMeta<OptionT>, setter: (newValue: OptionT | null) => void) {
    if (action.action !== 'select-option') {
        return;
    }
    setter(value);
}

function handleNumberChange(value: string | number, min: number | null, max: number | null, setter: (newValue: number) => void) {
    let intVal;
    if (typeof value !== 'number') {
        intVal = parseInt(value);
    } else {
        intVal = value;
    }
    if ((min !== null && intVal < min) || (max !== null && intVal > max)) {
        // Out of bounds
        return;
    }
    setter(intVal);
}

function findBySlug<T extends ApiRecord.EntityHasSlug>(needle: string, haystack: Array<T>): T | null {
    for (const check of haystack) {
        if (check.slug === needle) {
            return check;
        }
    }
    return null;
}

async function fetchBalls(currentVersion: ApiRecord.Version, page: number = 1): Promise<Array<ApiRecord.Item.ItemInVersionGroup.CaptureRate>> {
    const response = await pktQuery<ApiRecord.HydraCollection<ApiRecord.Item.ItemInVersionGroup.CaptureRate>>('item_in_version_groups', {
        'category.slug': [
            'apricorn-balls',
            'pokeballs',
            'special-balls',
            'standard-balls',
        ],
        versionGroup: currentVersion.versionGroup,
        groups: ['capture_rate'],
        page: page,
    }, currentVersion);
    if (response.data['hydra:view']['hydra:next']) {
        // Fetch next
        return response.data['hydra:member'].concat(await fetchBalls(currentVersion, page + 1));
    }
    return response.data['hydra:member'];
}

// TODO: Can this list be fetched via SQL in the backend's DataProvider?  Easy to get Pokemon that evolve with the stone, harder to get their relatives in a way that can be paged.
const moonStonePokemonSlugs = new Set([
    'nidoran-f',
    'nidorina',
    'nidoqueen',
    'nidoran-m',
    'nidorino',
    'nidoking',
    'cleffa',
    'clefairy',
    'clefable',
    'igglybuff',
    'jigglypuff',
    'wigglytuff',
    'skitty',
    'delcatty',
    'munna',
    'musharna',
]);

async function fetchPokemon(currentVersion: ApiRecord.Version, page: number = 1): Promise<Array<ApiRecord.Pokemon.Pokemon.CaptureRate>> {
    const response = await pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.Pokemon.CaptureRate>>('pokemon', {
        'species.versionGroup': currentVersion.versionGroup,
        groups: ['capture_rate'],
        page: page,
        itemsPerPage: 100,
    }, currentVersion);
    const data = response.data['hydra:member'].map(pokemon => {
        // Moon ball is bugged in Gen 2, so this isn't helpful there.
        pokemon.moonStone = currentVersion.generationNumber > 2 && moonStonePokemonSlugs.has(pokemon.slug);
        return pokemon;
    });
    if (response.data['hydra:view']['hydra:next']) {
        // Fetch next
        return data.concat(await fetchPokemon(currentVersion, page + 1));
    }
    return data;
}

async function fetchEncounterMethods(currentVersion: ApiRecord.Version, page: number = 1): Promise<Array<ApiRecord.Pokemon.EncounterMethod>> {
    const response = await pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.EncounterMethod>>('encounter_methods', {
        version: currentVersion.id,
        page: page,
    }, currentVersion);
    if (response.data['hydra:view']['hydra:next']) {
        // Fetch next
        return response.data['hydra:member'].concat(await fetchEncounterMethods(currentVersion, page + 1));
    }
    return response.data['hydra:member'];
}

async function fetchGenders(currentVersion: ApiRecord.Version, page: number = 1): Promise<Array<ApiRecord.Pokemon.Gender>> {
    const response = await pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.Gender>>('genders', {
        page: page,
    }, currentVersion);
    if (response.data['hydra:view']['hydra:next']) {
        // Fetch next
        return response.data['hydra:member'].concat(await fetchGenders(currentVersion, page + 1));
    }
    return response.data['hydra:member'];
}

async function fetchTimesOfDay(currentVersion: ApiRecord.Version, page: number = 1): Promise<Array<ApiRecord.TimeOfDay>> {
    const response = await pktQuery<ApiRecord.HydraCollection<ApiRecord.TimeOfDay>>('time_of_days', {
        versionGroup: currentVersion.versionGroup,
        page: page,
    }, currentVersion);
    if (response.data['hydra:view']['hydra:next']) {
        // Fetch next
        return response.data['hydra:member'].concat(await fetchTimesOfDay(currentVersion, page + 1));
    }
    return response.data['hydra:member'];
}

type CaptureRateTableProps = CaptureRateCalcUserState & Required<Pick<CaptureRateCalcDataState, 'balls'>>;

interface Chance {
    ball: ApiRecord.Item.ItemInVersionGroup.CaptureRate
    /** The conditions leading to this capture rate */
    conditions: string
    /** The percent chance, 0-100 inclusive */
    chance: number
    /** A number less than, equal to, or greater than 0 to describe a negative, neutral, or positive effect on the capture rate */
    effect: number
    /** Used to describe how a special ball has affected the capture rate. */
    note?: string
}

interface BallEffect {
    multiplier: number
    effect: number
    note?: string
}

function CaptureRateTable(props: CaptureRateTableProps) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {
        balls,
        pokemonAttacking,
        pokemonDefending,
        method,
        levelAttacking,
        levelDefending,
        genderAttacking,
        genderDefending,
        timeOfDay,
        inDarkGrass,
        pokedexCount,
        capturePower,
        hp,
    } = props;

    /** Gen 1 */
    const calcGen1 = useCallback(() => {
        // Gen 1 works very differently than later generations.  This runs the algorithm used in the games.
        if (!pokemonDefending) {
            return null;
        }
        const statusModifiers: Record<string, number> = {
            'Poisoned, burned, or paralyzed': 12,
            'Frozen or asleep': 25,
            '': 0,
        };
        const ballModifiers: Record<string, number> = {
            'poke-ball': 255,
            'great-ball': 200,
        };
        const pokemonBallModifiers: Record<string, number> = {
            'great-ball': 8,
        };

        const chances: Array<Chance> = [];
        for (const ball of balls) {
            const pokemonBallModifier = pokemonBallModifiers[ball.slug] ?? 12;
            const ballModifier = ballModifiers[ball.slug] ?? 150;
            const p1 = ((pokemonDefending.captureRate + 1) / (ballModifier + 1)) * ((102000 / (hp * pokemonBallModifier) + 1) / 256);
            for (const [status, statusModifier] of Object.entries(statusModifiers)) {
                const p0 = statusModifier / (ballModifier + 1);
                chances.push({
                    ball: ball,
                    conditions: status,
                    chance: Math.min(Math.floor((p0 + p1) * 100), 100),
                    effect: 0,
                });
            }
        }

        return chances;
    }, [balls, pokemonDefending, hp]);

    /** Gen 2 */
    const calcGen2 = useCallback(() => {
        if (!pokemonDefending || !pokemonAttacking || !method || !genderAttacking || !genderDefending) {
            return null;
        }
        const ballEffects = (ball: string): BallEffect => {
            switch (ball) {
                // Moon ball is included here because of a bug in Gen 2
                case 'poke-ball':
                case 'moon-ball':
                case 'friend-ball':
                    return {multiplier: 1, effect: 0};
                case 'great-ball':
                case 'safari-ball':
                case 'sport-ball':
                    return {multiplier: 1.5, effect: 0};
                case 'ultra-ball':
                    return {multiplier: 2, effect: 0};
                case 'level-ball':
                    if (levelAttacking <= levelDefending) {
                        return {multiplier: 1, effect: 0};
                    } else if (levelAttacking < (2 * levelDefending)) {
                        return {
                            multiplier: 2,
                            effect: 1,
                            note: '2× capture rate because the opponent\'s level is lower',
                        };
                    } else if (levelAttacking < (4 * levelDefending)) {
                        return {
                            multiplier: 4,
                            effect: 1,
                            note: '4× capture rate because the opponent\'s level is lower',
                        };
                    }
                    return {multiplier: 8, effect: 1, note: '8× capture rate because the opponent\'s level is lower'};
                case 'lure-ball':
                    switch (method.slug) {
                        case 'old-rod':
                        case 'good-rod':
                        case 'super-rod':
                            return {multiplier: 3, effect: 1, note: '3× capture rate while fishing'};
                    }
                    break;
                case 'love-ball':
                    const sameSpecies = pokemonAttacking === pokemonDefending;
                    const sameGender = ![genderAttacking.slug, genderDefending.slug].includes('genderless')
                        && genderAttacking === genderDefending;
                    if (sameSpecies && sameGender) {
                        return {
                            multiplier: 8,
                            effect: 1,
                            note: '8× capture rate against an opponent of the same species and gender',
                        };
                    }
                    break;
                case 'heavy-ball':
                    if (pokemonDefending.weightGrams <= 102300) {
                        return {
                            multiplier: 0.8,
                            effect: -1,
                            note: '0.8× capture rate because the opponent is not heavy',
                        };
                    } else if (pokemonDefending.weightGrams <= 204700) {
                        return {multiplier: 1, effect: 0};
                    } else if (pokemonDefending.weightGrams <= 307100) {
                        return {
                            multiplier: 1.2,
                            effect: 1,
                            note: '1.2× capture rate because the opponent is somewhat heavy',
                        };
                    } else if (pokemonDefending.weightGrams <= 409500) {
                        return {
                            multiplier: 1.3,
                            effect: 1,
                            note: '1.3× capture rate because the opponent is moderately heavy',
                        };
                    }
                    return {multiplier: 1.4, effect: 1, note: '1.4× capture rate because the opponent is quite heavy'};
                case 'fast-ball':
                    switch (pokemonDefending.slug) {
                        case 'magnemite':
                        case 'grimer':
                        case 'tangela':
                            return {multiplier: 4, effect: 1, note: '4× capture rate because of a bug in the game'};
                    }
                    break;
            }
            return {multiplier: 1, effect: 0};
        };

        // The game uses a lookup table for this instead of real logarithms.
        const shakeComparator = (catchRate: number) => {
            if (catchRate <= 1) {
                return 63;
            } else if (catchRate <= 2) {
                return 75;
            } else if (catchRate <= 3) {
                return 84;
            } else if (catchRate <= 4) {
                return 90;
            } else if (catchRate <= 5) {
                return 95;
            } else if (catchRate <= 7) {
                return 103;
            } else if (catchRate <= 10) {
                return 113;
            } else if (catchRate <= 15) {
                return 116;
            } else if (catchRate <= 20) {
                return 134;
            } else if (catchRate <= 30) {
                return 149;
            } else if (catchRate <= 40) {
                return 160;
            } else if (catchRate <= 50) {
                return 169;
            } else if (catchRate <= 60) {
                return 177;
            } else if (catchRate <= 80) {
                return 191;
            } else if (catchRate <= 100) {
                return 201;
            } else if (catchRate <= 120) {
                return 211;
            } else if (catchRate <= 140) {
                return 220;
            } else if (catchRate <= 160) {
                return 227;
            } else if (catchRate <= 180) {
                return 234;
            } else if (catchRate <= 200) {
                return 240;
            } else if (catchRate <= 220) {
                return 246;
            } else if (catchRate <= 240) {
                return 251;
            } else if (catchRate <= 254) {
                return 253;
            }
            return 255;
        };

        const statusModifiers = {
            'Frozen or asleep': 10,
            '': 0,
        };

        const chances: Array<Chance> = [];
        for (const ball of balls) {
            for (const [status, statusModifier] of Object.entries(statusModifiers)) {
                const ballEffect = ballEffects(ball.slug);
                const pokemonBallRate = pokemonDefending.captureRate * ballEffect.multiplier;
                const catchRate = Math.min(Math.max(((300 - 2 * hp) * pokemonBallRate) / 300, 1) + statusModifier, 255);
                const chance = Math.floor(Math.pow(shakeComparator(catchRate) / 255, 4) * 100);
                chances.push({
                    ball: ball,
                    conditions: status,
                    chance: chance,
                    note: ballEffect.note,
                    effect: ballEffect.effect,
                });
            }
        }

        return chances;
    }, [balls, genderAttacking, genderDefending, hp, levelAttacking, levelDefending, method, pokemonAttacking, pokemonDefending]);

    /** Gen 3/4 */
    const calcGen34 = useCallback(() => {
        if (!pokemonDefending || !method) {
            return null;
        }

        const calcChance = (ball: ApiRecord.Item.ItemInVersionGroup.CaptureRate, ballMultiplier: number, statusModifier: number) => {
            let useBallMultiplier = ballMultiplier;
            let extra = 1;
            if (ball.category.slug === 'apricorn-balls') {
                useBallMultiplier = 1;
                extra = ballMultiplier;
            }
            const catchRate = (((300 - 2 * hp) * pokemonDefending.captureRate * useBallMultiplier) / 300) * statusModifier * extra;
            if (catchRate >= 255) {
                // The game performs no checks
                return 100;
            } else {
                return Math.min(Math.floor(Math.pow((1048560 / Math.sqrt(Math.sqrt(16711680 / catchRate))) / 65535, 4) * 100), 100);
            }
        };

        /** Figure the multiplier and related info about the ball's effect */
        const ballEffects = (ball: ApiRecord.Item.ItemInVersionGroup.CaptureRate, statusModifier: number): BallEffect | null => {
            switch (ball.slug) {
                case 'poke-ball':
                case 'premier-ball':
                case 'luxury-ball':
                case 'heal-ball':
                case 'friend-ball':
                    return {multiplier: 1, effect: 0};
                case 'great-ball':
                case 'safari-ball':
                case 'sport-ball':
                    return {multiplier: 1.5, effect: 0};
                case 'ultra-ball':
                    return {multiplier: 2, effect: 0};
                case 'net-ball':
                    if (pokemonDefending.types.some(type => ['bug', 'water'].includes(type.type.slug))) {
                        return {
                            multiplier: 3,
                            effect: 1,
                            note: '3× capture rate because the opponent is a Bug or Water type Pokémon',
                        };
                    }
                    break;
                case 'nest-ball':
                    const multiplier = Math.max((40 - levelDefending) / 10, 1);
                    if (multiplier > 1) {
                        return {
                            multiplier: multiplier,
                            effect: 1,
                            note: `${multiplier}× capture rate because the opponent has a low level`,
                        };
                    }
                    break;
                case 'repeat-ball':
                    return {
                        multiplier: 3,
                        effect: 0,
                        note: `3× capture rate (${calcChance(ball, 3, statusModifier)}%) against previously-caught Pokémon; 1× (${calcChance(ball, 1, statusModifier)}%) otherwise`,
                    };
                case 'timer-ball':
                    return {
                        multiplier: 4,
                        effect: 0,
                        note: `1–4× capture rate (${calcChance(ball, 1, statusModifier)}–${calcChance(ball, 4, statusModifier)}%) as turn count increases, up to 30 turns`,
                    };
                case 'dive-ball':
                    if (currentVersion.generationNumber === 3) {
                        if (pokemonDefending.types.some(type => type.type.slug === 'water')) {
                            // Only show this detail against water Pokemon
                            return {
                                multiplier: 3.5,
                                effect: 0,
                                note: `3.5× capture rate (${calcChance(ball, 3.5, statusModifier)}%) while diving; 1× (${calcChance(ball, 1, statusModifier)}%) otherwise`,
                            };
                        }
                        break;
                    }
                    switch (method.slug) {
                        case 'old-rod':
                        case 'good-rod':
                        case 'super-rod':
                        case 'surf':
                            return {multiplier: 3.5, effect: 1, note: '3.5× capture rate while fishing or surfing'};
                    }
                    break;
                case 'dusk-ball':
                    if (!timeOfDay) {
                        return null;
                    }
                    if (timeOfDay.slug === 'night') {
                        // Always gives a capture rate bonus
                        return {multiplier: 3.5, effect: 1, note: '3.5× capture rate at night'};
                    }
                    return {
                        multiplier: 3.5,
                        effect: 0,
                        note: `3.5× capture rate (${calcChance(ball, 3.5, statusModifier)}%) inside a cave; 1× (${calcChance(ball, 1, statusModifier)}%) otherwise`,
                    };
                case 'quick-ball':
                    return {
                        multiplier: 4,
                        effect: 0,
                        note: `4× capture rate (${calcChance(ball, 4, statusModifier)}%) on the first turn; 1× (${calcChance(ball, 1, statusModifier)}%) otherwise`,
                    };
                case 'level-ball':
                    if (levelAttacking <= levelDefending) {
                        return {multiplier: 1, effect: 0};
                    } else if (levelAttacking < (2 * levelDefending)) {
                        return {
                            multiplier: 2,
                            effect: 1,
                            note: '2× capture rate because the opponent\'s level is lower',
                        };
                    } else if (levelAttacking < (4 * levelDefending)) {
                        return {
                            multiplier: 4,
                            effect: 1,
                            note: '4× capture rate because the opponent\'s level is moderately lower',
                        };
                    }
                    return {
                        multiplier: 8,
                        effect: 1,
                        note: '8× capture rate because the opponent\'s level is far lower',
                    };
                case 'lure-ball':
                    switch (method.slug) {
                        case 'old-rod':
                        case 'good-rod':
                        case 'super-rod':
                            return {multiplier: 3, effect: 1, note: '3.5× capture rate while fishing'};
                    }
                    break;
                case 'love-ball':
                    if (!genderAttacking || !genderDefending) {
                        return null;
                    }
                    const sameSpecies = pokemonAttacking === pokemonDefending;
                    const oppositeGender = ![genderAttacking.slug, genderDefending.slug].includes('genderless')
                        && genderAttacking !== genderDefending;
                    if (sameSpecies && oppositeGender) {
                        return {
                            multiplier: 8,
                            effect: 1,
                            note: '8× capture rate against an opponent of the same species and opposite gender',
                        };
                    }
                    break;
                case 'heavy-ball':
                    if (pokemonDefending.weightGrams <= 204700) {
                        return {
                            multiplier: 0.8,
                            effect: -1,
                            note: '0.8× capture rate because the opponent is not heavy',
                        };
                    } else if (pokemonDefending.weightGrams <= 307100) {
                        return {
                            multiplier: 1.2,
                            effect: 1,
                            note: '1.2× capture rate because the opponent is somewhat heavy',
                        };
                    } else if (pokemonDefending.weightGrams <= 409500) {
                        return {
                            multiplier: 1.3,
                            effect: 1,
                            note: '1.3× capture rate because the opponent is moderately heavy',
                        };
                    }
                    return {multiplier: 1.4, effect: 1, note: '1.4× capture rate because the opponent is quite heavy'};
                case 'fast-ball':
                    if (pokemonDefending.speed.baseValue >= 100) {
                        return {
                            multiplier: 4,
                            effect: 1,
                            note: '4× capture rate because the opponent has a high base speed stat',
                        };
                    }
                    break;
                case 'moon-ball':
                    // This isn't bugged anymore
                    if (pokemonDefending.moonStone) {
                        return {
                            multiplier: 4,
                            effect: 1,
                            note: '4× capture rate because the opponent is related to a Pokémon that evolves with a Moon Stone',
                        };
                    }
                    break;
            }
            return {multiplier: 1, effect: 0};
        };

        const statusModifiers = {
            'Frozen or asleep': 2,
            'Poisoned, burned, or paralyzed': 1.5,
            '': 1,
        };

        const chances: Array<Chance> = [];
        for (const ball of balls) {
            for (const [status, statusModifier] of Object.entries(statusModifiers)) {
                const ballEffect = ballEffects(ball, statusModifier);
                if (!ballEffect) {
                    // Not ready yet
                    return null;
                }
                const chance = calcChance(ball, ballEffect.multiplier, statusModifier);
                chances.push({
                    ball: ball,
                    conditions: status,
                    chance: chance,
                    note: ballEffect.note,
                    effect: ballEffect.effect,
                });
            }
        }

        return chances;
    }, [currentVersion, balls, genderAttacking, genderDefending, hp, levelAttacking, levelDefending, method, pokemonAttacking, pokemonDefending, timeOfDay]);

    /** Gen 5/6 */
    const calcGen56 = useCallback(() => {
        if (!pokemonDefending) {
            return null;
        }

        const calcChance = (ball: ApiRecord.Item.ItemInVersionGroup.CaptureRate, ballMultiplier: number, statusModifier: number) => {
            function capturePowerModifier() {
                switch (capturePower) {
                    case 1:
                        return currentVersion.generationNumber === 5 ? 1.1 : 1.5;
                    case 2:
                        return currentVersion.generationNumber === 5 ? 1.2 : 2;
                    case 3:
                        return currentVersion.generationNumber === 5 ? 1.3 : 2.5;
                }
                return 1;
            }

            function darkGrassModifier() {
                if (!inDarkGrass || pokedexCount === 0) {
                    return 1;
                } else if (pokedexCount < 30) {
                    return 1229 / 4096;
                } else if (pokedexCount <= 150) {
                    return 2048 / 4096;
                } else if (pokedexCount <= 300) {
                    return 2867 / 4096;
                } else if (pokedexCount <= 450) {
                    return 3277 / 4096;
                } else if (pokedexCount <= 600) {
                    return 3686 / 4096;
                }
                return 1;
            }

            const hpMultiplier = 300 - 2 * hp * darkGrassModifier();
            const catchRate = ((hpMultiplier * pokemonDefending.captureRate * ballMultiplier) / 300) * statusModifier * capturePowerModifier();
            if (catchRate >= 255) {
                // The game performs no checks
                return 100;
            } else {
                let chance;
                if (currentVersion.generationNumber === 5) {
                    chance = Math.pow((65536 / Math.sqrt(Math.sqrt(255 / catchRate))) / 65535, 4);
                } else {
                    chance = Math.pow((65536 / Math.pow(255 / catchRate, 0.1875)) / 65535, 4);
                }
                return Math.min(Math.floor(chance * 100), 100);
            }
        };

        /**
         * Figure the multiplier and related info about the ball's effect
         *
         * @param ball
         * @param statusModifier
         * @returns {[number, string, number]|[number, null, number]} Multiplier, note, effect
         */
        const ballEffects = (ball: ApiRecord.Item.ItemInVersionGroup.CaptureRate, statusModifier: number): BallEffect | null => {
            switch (ball.slug) {
                case 'poke-ball':
                case 'premier-ball':
                case 'luxury-ball':
                case 'heal-ball':
                case 'friend-ball':
                    return {multiplier: 1, effect: 0};
                case 'great-ball':
                case 'safari-ball':
                case 'sport-ball':
                    return {multiplier: 1.5, effect: 0};
                case 'ultra-ball':
                    return {multiplier: 2, effect: 0};
                case 'net-ball':
                    if (pokemonDefending.types.some(type => ['bug', 'water'].includes(type.type.slug))) {
                        return {
                            multiplier: 3,
                            effect: 1,
                            note: '3× capture rate because the opponent is a Bug or Water type Pokémon',
                        };
                    }
                    break;
                case 'nest-ball':
                    let rate;
                    if (currentVersion.generationNumber < 6) {
                        rate = Math.max((40 - levelDefending) / 10, 1);
                    } else if (levelDefending < 30) {
                        rate = Math.max((41 - levelDefending) / 10, 1);
                    } else {
                        rate = 1;
                    }
                    if (rate > 1) {
                        return {
                            multiplier: rate,
                            effect: 1,
                            note: `${rate}× capture rate because the opponent has a low level`,
                        };
                    }
                    break;
                case 'repeat-ball':
                    return {
                        multiplier: 3,
                        effect: 0,
                        note: `3× capture rate (${calcChance(ball, 3, statusModifier)}%) against previously-caught Pokémon; 1× (${calcChance(ball, 1, statusModifier)}%) otherwise)`,
                    };
                case 'timer-ball':
                    return {
                        multiplier: 4,
                        effect: 0,
                        note: `1–4× capture rate (${calcChance(ball, 1, statusModifier)}–${calcChance(ball, 4, statusModifier)}%) as turn count increases, up to 30 turns`,
                    };
                case 'dive-ball':
                    if (!method) {
                        return null;
                    }
                    switch (method.slug) {
                        case 'old-rod':
                        case 'good-rod':
                        case 'super-rod':
                        case 'surf':
                            return {multiplier: 3.5, effect: 1, note: '3.5× capture rate while fishing or surfing'};
                    }
                    break;
                case 'dusk-ball':
                    if (!timeOfDay) {
                        return null;
                    }
                    if (timeOfDay.slug === 'night') {
                        // Always gives a capture rate bonus
                        return {multiplier: 3.5, effect: 1, note: '3.5× capture rate at night'};
                    }
                    return {
                        multiplier: 3.5,
                        effect: 0,
                        note: `3.5× capture rate (${calcChance(ball, 3.5, statusModifier)}%) inside a cave; 1× (${calcChance(ball, 1, statusModifier)}%) otherwise`,
                    };
                case 'quick-ball':
                    return {
                        multiplier: 4,
                        effect: 0,
                        note: `4× capture rate (${calcChance(ball, 4, statusModifier)}%) on the first turn; 1× (${calcChance(ball, 1, statusModifier)}%) otherwise`,
                    };
                case 'level-ball':
                    if (levelAttacking <= levelDefending) {
                        return {multiplier: 1, effect: 0};
                    } else if (levelAttacking < (2 * levelDefending)) {
                        return {
                            multiplier: 2,
                            effect: 1,
                            note: '2× capture rate because the opponent\'s level is lower',
                        };
                    } else if (levelAttacking < (4 * levelDefending)) {
                        return {
                            multiplier: 4,
                            effect: 1,
                            note: '4× capture rate because the opponent\'s level is moderately lower',
                        };
                    }
                    return {
                        multiplier: 8,
                        effect: 1,
                        note: '8× capture rate because the opponent\'s level is far lower',
                    };
                case 'lure-ball':
                    if (!method) {
                        return null;
                    }
                    switch (method.slug) {
                        case 'old-rod':
                        case 'good-rod':
                        case 'super-rod':
                            const multiplier = currentVersion.generationNumber < 7 ? 3 : 5;
                            return {
                                multiplier: multiplier,
                                effect: 1,
                                note: `${multiplier}× capture rate while fishing`,
                            };
                    }
                    break;
                case 'love-ball':
                    if (!pokemonAttacking || !genderAttacking || !genderDefending) {
                        return null;
                    }
                    const sameSpecies = pokemonAttacking === pokemonDefending;
                    const oppositeGender = ![genderAttacking.slug, genderDefending.slug].includes('genderless')
                        && genderAttacking !== genderDefending;
                    if (sameSpecies && oppositeGender) {
                        return {
                            multiplier: 8,
                            effect: 1,
                            note: '8× capture rate against an opponent of the same species and opposite gender',
                        };
                    }
                    break;
                case 'heavy-ball':
                    if (currentVersion.generationNumber < 7) {
                        if (pokemonDefending.weightGrams <= 204700) {
                            return {
                                multiplier: 0.8,
                                effect: -1,
                                note: '0.8× capture rate because the opponent is not heavy',
                            };
                        } else if (pokemonDefending.weightGrams <= 307100) {
                            return {
                                multiplier: 1.2,
                                effect: 1,
                                note: '1.2× capture rate because the opponent is somewhat heavy',
                            };
                        } else if (pokemonDefending.weightGrams <= 409500) {
                            return {
                                multiplier: 1.3,
                                effect: 1,
                                note: '1.3× capture rate because the opponent is moderately heavy',
                            };
                        }
                        return {
                            multiplier: 1.4,
                            effect: 1,
                            note: '1.4× capture rate because the opponent is quite heavy',
                        };
                    }
                    if (pokemonDefending.weightGrams <= 99900) {
                        return {
                            multiplier: 0.8,
                            effect: -1,
                            note: '0.8× capture rate because the opponent is not heavy',
                        };
                    } else if (pokemonDefending.weightGrams <= 199900) {
                        return {multiplier: 1, effect: 0};
                    } else if (pokemonDefending.weightGrams <= 299900) {
                        return {
                            multiplier: 1.2,
                            effect: 1,
                            note: '1.2× capture rate because the opponent is somewhat heavy',
                        };
                    }
                    return {multiplier: 1.3, effect: 1, note: '1.3× capture rate because the opponent is quite heavy'};
                case 'fast-ball':
                    if (pokemonDefending.speed.baseValue >= 100) {
                        return {
                            multiplier: 4,
                            effect: 1,
                            note: '4× capture rate because the opponent has a high base speed stat',
                        };
                    }
                    break;
                case 'moon-ball':
                    if (pokemonDefending.moonStone) {
                        return {
                            multiplier: 4,
                            effect: 1,
                            note: '4× capture rate because the opponent is related to a Pokémon that evolves with a Moon Stone',
                        };
                    }
                    break;
                case 'dream-ball':
                    return {
                        multiplier: 4,
                        effect: 0,
                        note: `4× capture rate (${calcChance(ball, 4, statusModifier)}) when the opponent is sleeping; 1× (${calcChance(ball, 1, statusModifier)}) otherwise`,
                    };
            }
            return {multiplier: 1, effect: 0};
        };

        const statusModifiers = {
            'Frozen or asleep': 2.5,
            'Poisoned, burned, or paralyzed': 1.5,
            '': 1,
        };

        const chances: Array<Chance> = [];
        for (const ball of balls) {
            for (const [status, statusModifier] of Object.entries(statusModifiers)) {
                const ballEffect = ballEffects(ball, statusModifier);
                if (!ballEffect) {
                    // Not ready
                    return null;
                }
                const chance = calcChance(ball, ballEffect.multiplier, statusModifier);
                chances.push({
                    ball: ball,
                    conditions: status,
                    chance: chance,
                    note: ballEffect.note,
                    effect: ballEffect.effect,
                });
            }
        }

        return chances;
    }, [currentVersion, balls, capturePower, genderAttacking, genderDefending, hp, inDarkGrass, levelAttacking, levelDefending, method, pokedexCount, pokemonAttacking, pokemonDefending, timeOfDay]);

    const captureRates: Array<Chance> | null = useMemo(() => {
        switch (currentVersion.generationNumber) {
            case 1:
                return calcGen1();
            case 2:
                return calcGen2();
            case 3:
            case 4:
                return calcGen34();
            default:
                return calcGen56();
        }
    }, [currentVersion, calcGen1, calcGen2, calcGen34, calcGen56]);

    const rowHighlight = useCallback((effect: number) => {
        if (effect < 0) {
            return 'table-warning';
        } else if (effect > 0) {
            return 'table-success';
        }
        return undefined;
    }, []);

    if (!captureRates) {
        return (
            <Alert variant="info">
                Select battle conditions to calculate the capture rate.
            </Alert>
        );
    }
    captureRates.sort((a, b) => b.chance - a.chance);

    return (
        <Table>
            <thead>
            <tr>
                <th>Ball</th>
                <th>Conditions</th>
                <th>Chance</th>
            </tr>
            </thead>
            {captureRates.map((chance) => (
                <tbody key={`${chance.ball.slug}-${chance.conditions}`} className={rowHighlight(chance.effect)}>
                <tr>
                    <th scope="rowgroup">
                        <ItemLabel item={chance.ball}/>
                    </th>
                    <td>{chance.conditions}</td>
                    <td>{chance.chance}%</td>
                </tr>
                {chance.note && (
                    <tr>
                        <td colSpan={3}>
                            <PktMarkdown>{chance.note}</PktMarkdown>
                        </td>
                    </tr>
                )}
                </tbody>
            ))}
        </Table>
    );
}
