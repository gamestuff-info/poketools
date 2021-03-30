import '../../../assets/styles/Gauge.scss';
import {sum} from 'mathjs';

// From the css; used to calculate the geometry of the circle and arcs.
const strokeWidth = 90;
const size = 360;
const center = size / 2;
const r = (size / 2) - (strokeWidth / 2);

interface RadialGaugeParams {
    value: number | Array<number>;
}

export default function RadialGauge(props: RadialGaugeParams) {
    let values = props.value;
    if (!Array.isArray(values)) {
        values = [values];
    }

    // Assume a percentage if only one value passed.
    const total = values.length > 1 ? sum(...values) : 100;

    // Calculate the arcs
    const paths: Array<string> = [];
    let previousAngle = 0.0;
    for (const value of values) {
        const endAngle = (value / total) * 360;
        paths.push(describeSvgArc(center, center, r, previousAngle, endAngle));
        previousAngle = endAngle;
    }

    let contents;
    if (values.length === 1 && values[0] === 100) {
        // A 360 degree arc will be calculated wrong, so draw a circle instead.
        contents = (<circle className="pkt-gauge-value-0" cx={center} cy={center} r={r}/>);
    } else {
        // Draw the arcs
        contents = (
            <g>
                <circle className="pkt-gauge-empty" cx={center} cy={center} r={r}/>
                {paths.map((path, index) => (
                    <path key={index} className={`pkt-gauge-value-${index}`} d={path}/>
                ))}
            </g>
        );
    }

    return (
        <div className="pkt-gauge-labeled">
            <svg xmlns="http://www.w3.org/2000/svg"
                 id="svg8"
                 version="1.1"
                 className="pkt-gauge-radial"
                 viewBox={`0 0 ${size} ${size}`}
                 preserveAspectRatio="xMidYMid meet"
                 aria-hidden="true"
            >
                <g transform={`rotate(-90, ${center}, ${center})`}>
                    {contents}
                </g>
            </svg>
            {values.length === 1 && (
                <div className="pkt-gauge-label">{values[0]}%</div>
            )}
        </div>
    );
}

/**
 * Create an SVG arc path.
 *
 * @param cx
 * @param cy
 * @param r
 * @param startAngle
 * @param endAngle
 *
 * @return The `d` attribute for an SVG path.
 */
function describeSvgArc(cx: number, cy: number, r: number, startAngle: number, endAngle: number) {
    const startPoint = polarToCartesian(cx, cy, r, endAngle);
    const endPoint = polarToCartesian(cx, cy, r, startAngle);
    const svgLargeArcFlag = endAngle - startAngle <= 180 ? 0 : 1;

    // SVG path "d" attribute
    return [
        // Move absolute to start point
        'M',
        startPoint.x,
        startPoint.y,
        // Create arc to end point
        'A',
        r,
        r,
        0,
        svgLargeArcFlag,
        0,
        endPoint.x,
        endPoint.y,
    ].join(' ');
}

/**
 * Convert polar coordinates to cartesian coordinates.
 *
 * @param x
 * @param y
 * @param r
 * @param angle
 */
function polarToCartesian(x: number, y: number, r: number, angle: number) {
    const angleRad = angle * (Math.PI / 180);
    return {
        x: x + (r * Math.cos(angleRad)),
        y: y + (r * Math.sin(angleRad)),
    };
}
