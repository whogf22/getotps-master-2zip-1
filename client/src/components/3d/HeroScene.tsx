import { useRef, useMemo, Suspense } from "react";
import { Canvas, useFrame, useThree } from "@react-three/fiber";
import { Stars, Float } from "@react-three/drei";
import * as THREE from "three";
import { SceneErrorBoundary } from "./SceneErrorBoundary";

function HexGrid() {
  const group = useRef<THREE.Group>(null);
  const count = 60;

  const hexes = useMemo(() => {
    const arr = [];
    for (let i = 0; i < count; i++) {
      const x = (Math.random() - 0.5) * 24;
      const y = (Math.random() - 0.5) * 16;
      const z = -8 - Math.random() * 12;
      const scale = 0.15 + Math.random() * 0.35;
      arr.push({ x, y, z, scale, speed: 0.3 + Math.random() * 0.7, phase: Math.random() * Math.PI * 2 });
    }
    return arr;
  }, []);

  useFrame((state) => {
    if (!group.current) return;
    group.current.children.forEach((child, i) => {
      const hex = hexes[i];
      if (!hex) return;
      const mat = (child as THREE.Mesh).material as THREE.MeshBasicMaterial;
      mat.opacity = 0.03 + Math.sin(state.clock.elapsedTime * hex.speed + hex.phase) * 0.025;
    });
  });

  return (
    <group ref={group}>
      {hexes.map((h, i) => (
        <mesh key={i} position={[h.x, h.y, h.z]} rotation={[0, 0, Math.PI / 6]} scale={h.scale}>
          <circleGeometry args={[1, 6]} />
          <meshBasicMaterial color="#0ea5e9" transparent opacity={0.04} wireframe />
        </mesh>
      ))}
    </group>
  );
}

function DataStreams() {
  const group = useRef<THREE.Group>(null);

  const streams = useMemo(() => {
    const arr = [];
    for (let i = 0; i < 8; i++) {
      const startX = (Math.random() - 0.5) * 20;
      const startY = 8 + Math.random() * 4;
      const endX = startX + (Math.random() - 0.5) * 6;
      const endY = -8 - Math.random() * 4;
      const pts: THREE.Vector3[] = [];
      for (let j = 0; j <= 30; j++) {
        const t = j / 30;
        pts.push(new THREE.Vector3(
          startX + (endX - startX) * t + Math.sin(t * Math.PI * 2) * 0.5,
          startY + (endY - startY) * t,
          -5 - Math.random() * 5
        ));
      }
      arr.push(pts);
    }
    return arr;
  }, []);

  useFrame((state) => {
    if (!group.current) return;
    group.current.children.forEach((child, i) => {
      const mat = (child as THREE.Line).material as THREE.LineBasicMaterial;
      mat.opacity = 0.02 + Math.sin(state.clock.elapsedTime * 0.5 + i * 0.8) * 0.015;
    });
  });

  return (
    <group ref={group}>
      {streams.map((pts, i) => {
        const geo = new THREE.BufferGeometry().setFromPoints(pts);
        return (
          <primitive
            key={i}
            object={new THREE.Line(geo, new THREE.LineBasicMaterial({
              color: i % 2 === 0 ? "#22d3ee" : "#818cf8",
              transparent: true,
              opacity: 0.03
            }))}
          />
        );
      })}
    </group>
  );
}

function GlobeCore() {
  const core = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (core.current) {
      core.current.rotation.y = state.clock.elapsedTime * 0.04;
      core.current.rotation.x = Math.sin(state.clock.elapsedTime * 0.02) * 0.1;
    }
  });

  return (
    <group ref={core} position={[3.5, 0, -4]}>
      <mesh>
        <sphereGeometry args={[2.8, 64, 64]} />
        <meshPhysicalMaterial
          color="#020a18"
          emissive="#041020"
          emissiveIntensity={1}
          metalness={0.7}
          roughness={0.3}
          clearcoat={0.5}
          clearcoatRoughness={0.15}
        />
      </mesh>
      <mesh>
        <sphereGeometry args={[2.82, 40, 40]} />
        <meshBasicMaterial color="#0ea5e9" wireframe transparent opacity={0.045} />
      </mesh>
      <mesh>
        <sphereGeometry args={[2.85, 24, 24]} />
        <meshBasicMaterial color="#22d3ee" wireframe transparent opacity={0.02} />
      </mesh>
      <mesh>
        <sphereGeometry args={[3.2, 32, 32]} />
        <meshBasicMaterial color="#0ea5e9" transparent opacity={0.015} side={THREE.BackSide} />
      </mesh>
      <GlobeDots />
      <GlobeArcs />
      <GlobeRings />
    </group>
  );
}

function GlobeDots() {
  const points = useRef<THREE.Points>(null);
  const count = 250;

  const positions = useMemo(() => {
    const pos = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      const theta = Math.random() * Math.PI * 2;
      const phi = Math.acos(2 * Math.random() - 1);
      const r = 2.86;
      pos[i * 3] = r * Math.sin(phi) * Math.cos(theta);
      pos[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
      pos[i * 3 + 2] = r * Math.cos(phi);
    }
    return pos;
  }, []);

  return (
    <points ref={points}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" array={positions} count={count} itemSize={3} />
      </bufferGeometry>
      <pointsMaterial size={0.03} color="#22d3ee" transparent opacity={0.6} sizeAttenuation />
    </points>
  );
}

function GlobeArcs() {
  const group = useRef<THREE.Group>(null);

  const arcs = useMemo(() => {
    const a = [];
    for (let i = 0; i < 6; i++) {
      const startAngle = (i / 6) * Math.PI * 2;
      const endAngle = startAngle + Math.PI * 0.5 + Math.random() * Math.PI * 0.5;
      const pts: THREE.Vector3[] = [];
      for (let j = 0; j <= 24; j++) {
        const t = j / 24;
        const angle = startAngle + (endAngle - startAngle) * t;
        const elevation = Math.sin(t * Math.PI) * 1.2;
        const r = 2.9 + elevation;
        pts.push(new THREE.Vector3(
          r * Math.cos(angle),
          r * Math.sin(angle) * 0.5,
          r * Math.sin(angle) * 0.866
        ));
      }
      a.push(pts);
    }
    return a;
  }, []);

  useFrame((state) => {
    if (!group.current) return;
    group.current.children.forEach((child, i) => {
      const mat = (child as THREE.Line).material as THREE.LineBasicMaterial;
      mat.opacity = 0.06 + Math.sin(state.clock.elapsedTime * 0.8 + i * 1.2) * 0.04;
    });
  });

  return (
    <group ref={group}>
      {arcs.map((pts, i) => {
        const geo = new THREE.BufferGeometry().setFromPoints(pts);
        const colors = ["#22d3ee", "#818cf8", "#34d399", "#f472b6", "#fb923c", "#22d3ee"];
        return (
          <primitive
            key={i}
            object={new THREE.Line(geo, new THREE.LineBasicMaterial({
              color: colors[i],
              transparent: true,
              opacity: 0.08
            }))}
          />
        );
      })}
    </group>
  );
}

function GlobeRings() {
  const r1 = useRef<THREE.Mesh>(null);
  const r2 = useRef<THREE.Mesh>(null);
  const r3 = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (r1.current) { r1.current.rotation.z = state.clock.elapsedTime * 0.08; r1.current.rotation.x = 0.5; }
    if (r2.current) { r2.current.rotation.z = -state.clock.elapsedTime * 0.06; r2.current.rotation.x = 1.2; }
    if (r3.current) { r3.current.rotation.z = state.clock.elapsedTime * 0.04; r3.current.rotation.x = 0.8; r3.current.rotation.y = 0.3; }
  });

  return (
    <>
      <mesh ref={r1}><torusGeometry args={[3.8, 0.008, 4, 120]} /><meshBasicMaterial color="#0ea5e9" transparent opacity={0.12} /></mesh>
      <mesh ref={r2}><torusGeometry args={[4.3, 0.005, 4, 120]} /><meshBasicMaterial color="#818cf8" transparent opacity={0.06} /></mesh>
      <mesh ref={r3}><torusGeometry args={[5.0, 0.004, 4, 120]} /><meshBasicMaterial color="#22d3ee" transparent opacity={0.03} /></mesh>
    </>
  );
}

function FloatingPanels() {
  return (
    <>
      <Float speed={1.2} rotationIntensity={0.1} floatIntensity={0.3}>
        <GlassPanel position={[-5, 2.5, -2]} width={1.8} height={1.1} rotation={[0, 0.2, 0]} color="#0ea5e9" />
      </Float>
      <Float speed={0.8} rotationIntensity={0.08} floatIntensity={0.25}>
        <GlassPanel position={[6.5, 2, -3]} width={1.4} height={0.8} rotation={[0, -0.25, 0]} color="#818cf8" />
      </Float>
      <Float speed={1.5} rotationIntensity={0.06} floatIntensity={0.2}>
        <GlassPanel position={[-4, -2.5, -1]} width={1.2} height={0.7} rotation={[0, 0.15, 0.05]} color="#22d3ee" />
      </Float>
      <Float speed={1} rotationIntensity={0.12} floatIntensity={0.35}>
        <GlassPanel position={[5, -1.8, -2.5]} width={1.6} height={0.9} rotation={[0, -0.18, -0.03]} color="#34d399" />
      </Float>
    </>
  );
}

function GlassPanel({ position, width, height, rotation, color }: {
  position: [number, number, number]; width: number; height: number;
  rotation?: [number, number, number]; color: string;
}) {
  return (
    <group position={position} rotation={rotation || [0, 0, 0]}>
      <mesh>
        <boxGeometry args={[width, height, 0.015]} />
        <meshPhysicalMaterial
          color="#050d1a"
          emissive={color}
          emissiveIntensity={0.08}
          metalness={0.15}
          roughness={0.1}
          transparent
          opacity={0.25}
          clearcoat={0.6}
          clearcoatRoughness={0.1}
        />
      </mesh>
      <mesh position={[0, height / 2 - 0.04, 0.01]}>
        <boxGeometry args={[width * 0.9, 0.015, 0.003]} />
        <meshBasicMaterial color={color} transparent opacity={0.4} />
      </mesh>
      <mesh position={[-width / 2 + 0.08, height / 2 - 0.04, 0.012]}>
        <sphereGeometry args={[0.02, 8, 8]} />
        <meshBasicMaterial color="#22c55e" />
      </mesh>
      {[0.15, 0.35, 0.55, 0.72].map((y, i) => (
        <mesh key={i} position={[-width / 2 + 0.12 + (width * 0.75) * Math.random(), height / 2 - height * y, 0.01]}>
          <boxGeometry args={[width * (0.3 + Math.random() * 0.4), 0.025, 0.003]} />
          <meshBasicMaterial color={color} transparent opacity={0.08 + Math.random() * 0.06} />
        </mesh>
      ))}
    </group>
  );
}

function PulseNodes() {
  const nodes = useMemo(() => {
    return Array.from({ length: 5 }, (_, i) => ({
      pos: [
        (Math.random() - 0.5) * 14,
        (Math.random() - 0.5) * 8,
        -3 - Math.random() * 6
      ] as [number, number, number],
      color: ["#22d3ee", "#818cf8", "#34d399", "#f472b6", "#fb923c"][i],
      delay: i * 0.6,
    }));
  }, []);

  return (
    <>
      {nodes.map((n, i) => (
        <PulseNode key={i} position={n.pos} color={n.color} delay={n.delay} />
      ))}
    </>
  );
}

function PulseNode({ position, color, delay }: { position: [number, number, number]; color: string; delay: number }) {
  const mesh = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (mesh.current) {
      const t = ((state.clock.elapsedTime + delay) % 3) / 3;
      const scale = 0.05 + t * 0.2;
      mesh.current.scale.set(scale, scale, scale);
      (mesh.current.material as THREE.MeshBasicMaterial).opacity = (1 - t) * 0.4;
    }
  });

  return (
    <mesh ref={mesh} position={position}>
      <sphereGeometry args={[1, 16, 16]} />
      <meshBasicMaterial color={color} transparent opacity={0.2} />
    </mesh>
  );
}

function SpaceDust() {
  const mesh = useRef<THREE.Points>(null);
  const count = 600;

  const positions = useMemo(() => {
    const pos = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      pos[i * 3] = (Math.random() - 0.5) * 40;
      pos[i * 3 + 1] = (Math.random() - 0.5) * 25;
      pos[i * 3 + 2] = -2 - Math.random() * 20;
    }
    return pos;
  }, []);

  useFrame((state) => {
    if (mesh.current) mesh.current.rotation.y = state.clock.elapsedTime * 0.005;
  });

  return (
    <points ref={mesh}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" array={positions} count={count} itemSize={3} />
      </bufferGeometry>
      <pointsMaterial size={0.015} color="#475569" transparent opacity={0.4} sizeAttenuation />
    </points>
  );
}

function CameraRig() {
  useFrame((state) => {
    const targetX = state.mouse.x * 1.2;
    const targetY = state.mouse.y * 0.6 + 0.3;
    state.camera.position.x += (targetX - state.camera.position.x) * 0.015;
    state.camera.position.y += (targetY - state.camera.position.y) * 0.015;
    state.camera.lookAt(1, 0, -3);
  });
  return null;
}

function Scene() {
  return (
    <>
      <color attach="background" args={["#020810"]} />
      <fog attach="fog" args={["#020810", 12, 30]} />

      <ambientLight intensity={0.05} />
      <pointLight position={[8, 5, 2]} intensity={5} color="#22d3ee" distance={25} decay={2} />
      <pointLight position={[-6, 4, -3]} intensity={3} color="#818cf8" distance={20} decay={2} />
      <pointLight position={[2, -5, 5]} intensity={2} color="#0ea5e9" distance={18} decay={2} />
      <pointLight position={[-2, 2, 8]} intensity={2.5} color="#22d3ee" distance={15} decay={2} />
      <spotLight position={[0, 10, 0]} angle={0.35} penumbra={0.9} intensity={1.5} color="#0a3d62" distance={25} />

      <Stars radius={80} depth={50} count={2500} factor={1.5} fade speed={0.2} />
      <SpaceDust />
      <HexGrid />
      <DataStreams />
      <GlobeCore />
      <FloatingPanels />
      <PulseNodes />
      <CameraRig />
    </>
  );
}

export function HeroScene() {
  return (
    <div className="absolute inset-0 w-full h-full" style={{ zIndex: 0 }}>
      <SceneErrorBoundary fallback={<div className="absolute inset-0 hero-fallback-bg" />}>
        <Canvas
          camera={{ position: [0, 0.3, 10], fov: 45 }}
          gl={{ antialias: true, alpha: false, powerPreference: "high-performance" }}
          dpr={[1, 1.5]}
        >
          <Suspense fallback={null}>
            <Scene />
          </Suspense>
        </Canvas>
      </SceneErrorBoundary>
    </div>
  );
}
