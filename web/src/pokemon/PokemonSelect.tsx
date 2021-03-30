import ReactSelect, {createFilter, Props as ReactSelectProps} from 'react-select';
import PokemonLabel, {PokemonLabelPokemon} from './PokemonLabel';

interface PokemonSelectProps extends Omit<ReactSelectProps<PokemonLabelPokemon>, 'options' | 'components'> {
    allPokemon: Array<PokemonLabelPokemon>
}

type PokemonSelectOption = PokemonLabelPokemon;

export default function PokemonSelect(props: PokemonSelectProps) {
    const {allPokemon} = props;
    const elementProps: Partial<ReactSelectProps<PokemonSelectOption>> = Object.assign({}, props);
    delete elementProps.allPokemon;

    return (
        <ReactSelect {...elementProps}
                     options={allPokemon}
                     formatOptionLabel={OptionLabelFormatted}
                     getOptionLabel={getOptionLabel}
                     getOptionValue={getOptionValue}
                     filterOption={createFilter({ignoreAccents: true})}
        />
    );
}

function OptionLabelFormatted(option: PokemonLabelPokemon) {
    return <PokemonLabel pokemon={option} noLink/>;
}

function getOptionLabel(option: PokemonLabelPokemon) {
    return option.name;
}

function getOptionValue(option: PokemonLabelPokemon) {
    return option.slug;
}
